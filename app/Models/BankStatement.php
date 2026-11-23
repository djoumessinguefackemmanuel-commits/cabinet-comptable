<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;
use PDO;

/**
 * Lignes de relevés bancaires importées et leur rapprochement avec les lignes d'écritures.
 * Convention : montant > 0 = encaissement (débit du compte de banque), < 0 = décaissement.
 * Une ligne est « rapprochée » quand entry_line_id est renseigné.
 */
class BankStatement extends Model
{
    private static bool $ready = false;

    public static function ensureSchema(): void
    {
        if (self::$ready) {
            return;
        }

        $pdo = Database::connect();
        $pg = defined('DB_DRIVER') && DB_DRIVER === 'pgsql';
        $id = $pg ? 'SERIAL PRIMARY KEY' : 'INT AUTO_INCREMENT PRIMARY KEY';

        try {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS bank_statement_lines (
                    id $id,
                    company_id INT NOT NULL,
                    account_id INT NOT NULL,
                    line_date DATE NOT NULL,
                    label VARCHAR(255) NOT NULL,
                    reference VARCHAR(100) NULL,
                    amount DECIMAL(15,2) NOT NULL,
                    entry_line_id INT NULL,
                    line_hash CHAR(40) NOT NULL,
                    batch VARCHAR(40) NULL,
                    reconciled_at TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    CONSTRAINT uq_bsl_hash UNIQUE (line_hash),
                    CONSTRAINT uq_bsl_entry_line UNIQUE (entry_line_id),
                    CONSTRAINT fk_bsl_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
                    CONSTRAINT fk_bsl_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
                    CONSTRAINT fk_bsl_entry_line FOREIGN KEY (entry_line_id) REFERENCES entry_lines(id) ON DELETE SET NULL
                )"
            );
            $pdo->exec('CREATE INDEX idx_bsl_company_account ON bank_statement_lines (company_id, account_id)');
        } catch (\Throwable $e) {
            // Déjà créé (index existant) : sans conséquence.
        }

        self::$ready = true;
    }

    /** Comptes de trésorerie (classe 5) des entreprises autorisées. */
    public static function bankAccounts(?array $companyIds): array
    {
        [$in, $params] = self::inClause($companyIds);
        if ($in === null) {
            return [];
        }

        return self::query(
            "SELECT a.id, a.company_id, a.account_number, a.name, c.name AS company_name
             FROM accounts a INNER JOIN companies c ON c.id = a.company_id
             WHERE a.account_number LIKE '5%' {$in}
             ORDER BY c.name, a.account_number",
            $params
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findLine(int $id): ?array
    {
        return self::query('SELECT * FROM bank_statement_lines WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /** Importe des lignes ; ignore celles déjà importées. @return array{added:int,duplicates:int} */
    public static function import(int $companyId, int $accountId, array $lines): array
    {
        self::ensureSchema();
        $batch = date('YmdHis') . bin2hex(random_bytes(3));
        $seen = [];
        $added = 0;
        $dup = 0;

        self::beginTransaction();
        try {
            foreach ($lines as $l) {
                $key = $companyId . '|' . $accountId . '|' . $l['date'] . '|' . number_format($l['amount'], 2, '.', '') . '|' . mb_strtolower($l['label']);
                $seen[$key] = ($seen[$key] ?? 0) + 1;
                $hash = sha1($key . '|' . $seen[$key]);

                $exists = self::query('SELECT 1 FROM bank_statement_lines WHERE line_hash = ?', [$hash])->fetchColumn();
                if ($exists) {
                    $dup++;
                    continue;
                }

                self::execute(
                    'INSERT INTO bank_statement_lines (company_id, account_id, line_date, label, reference, amount, line_hash, batch)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                    [$companyId, $accountId, $l['date'], $l['label'], $l['reference'], $l['amount'], $hash, $batch]
                );
                $added++;
            }
            self::commit();
        } catch (\Throwable $e) {
            self::rollback();
            throw $e;
        }

        return ['added' => $added, 'duplicates' => $dup];
    }

    public static function lines(int $companyId, int $accountId, bool $reconciled): array
    {
        self::ensureSchema();

        return self::query(
            'SELECT b.*, e.reference AS entry_reference, e.entry_date, e.description AS entry_description
             FROM bank_statement_lines b
             LEFT JOIN entry_lines el ON el.id = b.entry_line_id
             LEFT JOIN entries e ON e.id = el.entry_id
             WHERE b.company_id = ? AND b.account_id = ? AND b.entry_line_id IS ' . ($reconciled ? 'NOT ' : '') . 'NULL
             ORDER BY b.line_date ' . ($reconciled ? 'DESC' : 'ASC') . ', b.id ' . ($reconciled ? 'DESC' : 'ASC') . ' LIMIT 500',
            [$companyId, $accountId]
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Lignes d'écritures du compte de banque pas encore rapprochées (montant = débit − crédit). */
    public static function openEntryLines(int $companyId, int $accountId): array
    {
        self::ensureSchema();

        return self::query(
            "SELECT el.id, e.entry_date, e.reference, COALESCE(NULLIF(el.description, ''), e.description) AS description,
                    (el.debit - el.credit) AS amount
             FROM entry_lines el
             INNER JOIN entries e ON e.id = el.entry_id
             WHERE e.company_id = ? AND el.account_id = ? AND e.status <> ?
               AND NOT EXISTS (SELECT 1 FROM bank_statement_lines b WHERE b.entry_line_id = el.id)
             ORDER BY e.entry_date ASC, el.id ASC LIMIT 2000",
            [$companyId, $accountId, ENTRY_CANCELLED]
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function entryLineForAccount(int $entryLineId, int $companyId, int $accountId): ?array
    {
        return self::query(
            'SELECT el.id, (el.debit - el.credit) AS amount
             FROM entry_lines el INNER JOIN entries e ON e.id = el.entry_id
             WHERE el.id = ? AND e.company_id = ? AND el.account_id = ?',
            [$entryLineId, $companyId, $accountId]
        )->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function match(int $lineId, int $entryLineId): bool
    {
        try {
            return self::execute(
                'UPDATE bank_statement_lines SET entry_line_id = ?, reconciled_at = NOW()
                 WHERE id = ? AND entry_line_id IS NULL',
                [$entryLineId, $lineId]
            );
        } catch (\PDOException $e) {
            return false; // ligne d'écriture déjà rapprochée ailleurs
        }
    }

    public static function unmatch(int $lineId): bool
    {
        return self::execute('UPDATE bank_statement_lines SET entry_line_id = NULL, reconciled_at = NULL WHERE id = ?', [$lineId]);
    }

    public static function deleteLine(int $lineId): bool
    {
        return self::execute('DELETE FROM bank_statement_lines WHERE id = ? AND entry_line_id IS NULL', [$lineId]);
    }

    /** Totaux : relevé, comptabilité, rapproché. */
    public static function summary(int $companyId, int $accountId): array
    {
        self::ensureSchema();
        $s = self::query(
            'SELECT COUNT(*) AS total, COALESCE(SUM(amount), 0) AS bank_balance,
                    SUM(CASE WHEN entry_line_id IS NULL THEN 1 ELSE 0 END) AS pending,
                    COALESCE(SUM(CASE WHEN entry_line_id IS NULL THEN amount ELSE 0 END), 0) AS pending_amount
             FROM bank_statement_lines WHERE company_id = ? AND account_id = ?',
            [$companyId, $accountId]
        )->fetch(PDO::FETCH_ASSOC);

        $book = self::query(
            'SELECT COALESCE(SUM(el.debit - el.credit), 0)
             FROM entry_lines el INNER JOIN entries e ON e.id = el.entry_id
             WHERE e.company_id = ? AND el.account_id = ? AND e.status <> ?',
            [$companyId, $accountId, ENTRY_CANCELLED]
        )->fetchColumn();

        return [
            'total' => (int) $s['total'],
            'pending' => (int) $s['pending'],
            'reconciled' => (int) $s['total'] - (int) $s['pending'],
            'bank_balance' => (float) $s['bank_balance'],
            'pending_amount' => (float) $s['pending_amount'],
            'book_balance' => (float) $book,
        ];
    }

    /** @return array{0:?string,1:array} fragment « AND a.company_id IN (...) » */
    private static function inClause(?array $companyIds): array
    {
        if ($companyIds === null) {
            return ['', []];
        }
        if ($companyIds === []) {
            return [null, []];
        }

        return ['AND a.company_id IN (' . implode(',', array_fill(0, count($companyIds), '?')) . ')', array_values($companyIds)];
    }
}
