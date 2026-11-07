<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Entry extends Model
{
    /**
     * Récupérer toutes les écritures comptables.
     */
    public static function getAll(): array
    {
        $sql = "
            SELECT
                e.*,
                j.name AS journal_name,
                j.code AS journal_code,
                c.name AS company_name,
                ex.name AS exercise_name,
                CONCAT(u.first_name, ' ', u.last_name) AS user_name
            FROM entries e
            INNER JOIN journals j ON j.id = e.journal_id
            INNER JOIN companies c ON c.id = e.company_id
            INNER JOIN exercises ex ON ex.id = e.exercise_id
            LEFT JOIN users u ON u.id = e.user_id
            ORDER BY e.entry_date DESC, e.id DESC
        ";

        return self::query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getByCompanies(array $companyIds): array
    {
        if ($companyIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($companyIds), '?'));
        $sql = "
            SELECT
                e.*,
                j.name AS journal_name,
                j.code AS journal_code,
                c.name AS company_name,
                ex.name AS exercise_name,
                CONCAT(u.first_name, ' ', u.last_name) AS user_name
            FROM entries e
            INNER JOIN journals j ON j.id = e.journal_id
            INNER JOIN companies c ON c.id = e.company_id
            INNER JOIN exercises ex ON ex.id = e.exercise_id
            LEFT JOIN users u ON u.id = e.user_id
            WHERE e.company_id IN ({$placeholders})
            ORDER BY e.entry_date DESC, e.id DESC
        ";

        return self::query($sql, $companyIds)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les écritures d'un exercice comptable.
     */
    public static function getByExercise(int $exerciseId): array
    {
        $sql = "
            SELECT
                e.*,
                j.name AS journal_name,
                j.code AS journal_code
            FROM entries e
            INNER JOIN journals j ON j.id = e.journal_id
            WHERE e.exercise_id = :exercise_id
            ORDER BY e.entry_date DESC, e.id DESC
        ";

        return self::query($sql, [
            'exercise_id' => $exerciseId
        ])->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les écritures actives d'un exercice.
     */
    public static function getAllActive(int $exerciseId): array
    {
        $sql = "
            SELECT
                e.*,
                j.name AS journal_name,
                j.code AS journal_code
            FROM entries e
            INNER JOIN journals j ON j.id = e.journal_id
            WHERE e.exercise_id = :exercise_id
            AND e.status = :status
            ORDER BY e.entry_date DESC, e.id DESC
        ";

        return self::query($sql, [
            'exercise_id' => $exerciseId,
            'status' => ENTRY_VALIDATED
        ])->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Rechercher une écriture par son identifiant.
     */
    public static function findById(int $id): ?array
    {
        $sql = "
            SELECT
                e.*,
                j.name AS journal_name,
                j.code AS journal_code,
                c.name AS company_name,
                ex.name AS exercise_name
            FROM entries e
            INNER JOIN journals j ON j.id = e.journal_id
            INNER JOIN companies c ON c.id = e.company_id
            INNER JOIN exercises ex ON ex.id = e.exercise_id
            WHERE e.id = :id
            LIMIT 1
        ";

        $result = self::query($sql, [
            'id' => $id
        ]);

        return $result->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Récupérer une écriture avec toutes ses lignes.
     */
    public static function findWithLines(int $id): ?array
    {
        $entry = self::findById($id);

        if (!$entry) {
            return null;
        }

        $sql = "
            SELECT
                el.*,
                a.account_number,
                a.name AS account_name
            FROM entry_lines el
            INNER JOIN accounts a ON a.id = el.account_id
            WHERE el.entry_id = :entry_id
            ORDER BY el.id ASC
        ";

        $entry['lines'] = self::query($sql, [
            'entry_id' => $id
        ]);

        return $entry;
    }

    /**
     * Générer une référence unique pour une écriture.
     */
    public static function generateReference(): string
    {
        return 'ECR-' . date('YmdHis') . '-' . strtoupper(
            substr(bin2hex(random_bytes(3)), 0, 6)
        );
    }

    /**
     * Créer une écriture avec ses lignes.
     */
    public static function createWithLines(array $data, array $lines): int|false
    {
        $pdo = self::getConnection();

        try {
            $pdo->beginTransaction();

            $reference = $data['reference'] ?? self::generateReference();

            $sql = "
                INSERT INTO entries (
                    company_id,
                    exercise_id,
                    journal_id,
                    user_id,
                    reference,
                    entry_date,
                    description,
                    status,
                    created_at,
                    updated_at
                ) VALUES (
                    :company_id,
                    :exercise_id,
                    :journal_id,
                    :user_id,
                    :reference,
                    :entry_date,
                    :description,
                    :status,
                    NOW(),
                    NOW()
                )
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                'company_id' => $data['company_id'],
                'exercise_id' => $data['exercise_id'],
                'journal_id' => $data['journal_id'],
                'user_id' => $data['user_id'],
                'reference' => $reference,
                'entry_date' => $data['entry_date'],
                'description' => $data['description'],
                'status' => $data['status'] ?? ENTRY_DRAFT
            ]);

            $entryId = (int) $pdo->lastInsertId();

            $lineSql = "
                INSERT INTO entry_lines (
                    entry_id,
                    account_id,
                    description,
                    debit,
                    credit,
                    created_at
                ) VALUES (
                    :entry_id,
                    :account_id,
                    :description,
                    :debit,
                    :credit,
                    NOW()
                )
            ";

            $lineStmt = $pdo->prepare($lineSql);

            foreach ($lines as $line) {
                $lineStmt->execute([
                    'entry_id' => $entryId,
                    'account_id' => $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0
                ]);
            }

            $pdo->commit();

            return $entryId;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log('Erreur création écriture : ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Modifier le statut d'une écriture.
     */
    public static function updateStatus(int $id, string $status): bool
    {
        $allowedStatuses = [
            ENTRY_DRAFT,
            ENTRY_VALIDATED,
            ENTRY_CANCELLED
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            return false;
        }

        $sql = "
            UPDATE entries
            SET
                status = :status,
                updated_at = NOW()
            WHERE id = :id
        ";

        return self::execute($sql, [
            'id' => $id,
            'status' => $status
        ]);
    }

    /**
     * Calculer le total des débits d'une écriture.
     */
    public static function getTotalDebit(int $entryId): float
    {
        $sql = "
            SELECT COALESCE(SUM(debit), 0) AS total
            FROM entry_lines
            WHERE entry_id = :entry_id
        ";

        $result = self::query($sql, [
            'entry_id' => $entryId
        ]);

        return (float) $result->fetchColumn();
    }

    /**
     * Calculer le total des crédits d'une écriture.
     */
    public static function getTotalCredit(int $entryId): float
    {
        $sql = "
            SELECT COALESCE(SUM(credit), 0) AS total
            FROM entry_lines
            WHERE entry_id = :entry_id
        ";

        $result = self::query($sql, [
            'entry_id' => $entryId
        ]);

        return (float) $result->fetchColumn();
    }

    /**
     * Compter les écritures d'un exercice.
     */
    public static function countByExercise(int $exerciseId): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM entries
            WHERE exercise_id = :exercise_id
        ";

        $result = self::query($sql, [
            'exercise_id' => $exerciseId
        ]);

        return (int) $result->fetchColumn();
    }
}