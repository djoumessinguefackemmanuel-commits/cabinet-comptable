<?php

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;
use PDO;

/**
 * Requêtes des états comptables (balance, grand livre) filtrées par période et compte.
 *
 * - seules les écritures validées sont prises en compte ;
 * - les résultats sont limités aux entreprises auxquelles l'utilisateur a accès.
 */
class ReportQuery
{
    private const MAX_LEDGER_ROWS = 2000;

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /** Date AAAA-MM-JJ valide, sinon null. */
    public static function date(mixed $value): ?string
    {
        if (!is_string($value) || $value === '') {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value ? $value : null;
    }

    /**
     * Balance : totaux débit/crédit et soldes par compte (comptes mouvementés uniquement).
     *
     * @return array{accounts: array<int, array<string, mixed>>, total_debit: float, total_credit: float, total_debit_balance: float, total_credit_balance: float}
     */
    public function trialBalance(?string $start, ?string $end): array
    {
        $scope = $this->companyScope('a.company_id');
        $empty = [
            'accounts' => [],
            'total_debit' => 0.0,
            'total_credit' => 0.0,
            'total_debit_balance' => 0.0,
            'total_credit_balance' => 0.0
        ];

        if ($scope === null) {
            return $empty;
        }

        $join = 'e.id = el.entry_id AND e.status = ?';
        $params = [ENTRY_VALIDATED];
        if ($start !== null) {
            $join .= ' AND e.entry_date >= ?';
            $params[] = $start;
        }
        if ($end !== null) {
            $join .= ' AND e.entry_date <= ?';
            $params[] = $end;
        }

        $sql = "
            SELECT
                a.id AS account_id,
                a.account_number,
                a.name AS account_name,
                a.type AS account_type,
                c.name AS company_name,
                COALESCE(SUM(el.debit), 0) AS total_debit,
                COALESCE(SUM(el.credit), 0) AS total_credit
            FROM accounts a
            INNER JOIN companies c ON c.id = a.company_id
            INNER JOIN (
                entry_lines el
                INNER JOIN entries e ON {$join}
            ) ON el.account_id = a.id
            WHERE a.is_active = 1 {$scope['sql']}
            GROUP BY a.id, a.account_number, a.name, a.type, c.name
            ORDER BY a.account_number ASC, c.name ASC
        ";

        $statement = $this->db->prepare($sql);
        $statement->execute(array_merge($params, $scope['params']));
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $companies = array_unique(array_column($rows, 'company_name'));
        $result = $empty;

        foreach ($rows as $row) {
            $debit = round((float) $row['total_debit'], 2);
            $credit = round((float) $row['total_credit'], 2);
            $difference = round($debit - $credit, 2);

            $result['accounts'][] = [
                'account_id' => (int) $row['account_id'],
                'account_number' => $row['account_number'],
                'account_type' => $row['account_type'],
                'account_name' => count($companies) > 1
                    ? $row['account_name'] . ' — ' . $row['company_name']
                    : $row['account_name'],
                'total_debit' => $debit,
                'total_credit' => $credit,
                'debit_balance' => $difference > 0 ? $difference : 0.0,
                'credit_balance' => $difference < 0 ? -$difference : 0.0
            ];

            $result['total_debit'] += $debit;
            $result['total_credit'] += $credit;
            $result['total_debit_balance'] += $difference > 0 ? $difference : 0.0;
            $result['total_credit_balance'] += $difference < 0 ? -$difference : 0.0;
        }

        foreach (['total_debit', 'total_credit', 'total_debit_balance', 'total_credit_balance'] as $key) {
            $result[$key] = round($result[$key], 2);
        }

        return $result;
    }

    /**
     * Bilan à une date donnée (écritures validées jusqu'à cette date incluse).
     *
     * @return array<string, mixed>
     */
    public function balanceSheet(string $date): array
    {
        $assets = [];
        $liabilities = [];
        $equityAccounts = 0.0;
        $revenues = 0.0;
        $expenses = 0.0;

        foreach ($this->trialBalance(null, $date)['accounts'] as $account) {
            $net = $account['total_debit'] - $account['total_credit'];
            $row = [
                'account_number' => $account['account_number'],
                'account_name' => $account['account_name']
            ];

            switch ($account['account_type']) {
                case ACCOUNT_ASSET:
                    $assets[] = $row + ['amount' => round($net, 2)];
                    break;
                case ACCOUNT_LIABILITY:
                    $liabilities[] = $row + ['amount' => round(-$net, 2)];
                    break;
                case ACCOUNT_EQUITY:
                    $equityAccounts += -$net;
                    break;
                case ACCOUNT_REVENUE:
                    $revenues += -$net;
                    break;
                case ACCOUNT_EXPENSE:
                    $expenses += $net;
                    break;
            }
        }

        return [
            'date' => $date,
            'assets' => $assets,
            'liabilities' => $liabilities,
            'total_assets' => round(array_sum(array_column($assets, 'amount')), 2),
            'total_liabilities' => round(array_sum(array_column($liabilities, 'amount')), 2),
            // Capitaux propres = comptes de capitaux + résultat de la période non encore clôturé.
            'equity' => round($equityAccounts + $revenues - $expenses, 2)
        ];
    }

    /**
     * Compte de résultat sur une période.
     *
     * @return array<string, mixed>
     */
    public function incomeStatement(?string $start, ?string $end): array
    {
        $revenues = [];
        $expenses = [];

        foreach ($this->trialBalance($start, $end)['accounts'] as $account) {
            $net = $account['total_debit'] - $account['total_credit'];
            $row = [
                'account_number' => $account['account_number'],
                'account_name' => $account['account_name']
            ];

            if ($account['account_type'] === ACCOUNT_REVENUE) {
                $revenues[] = $row + ['amount' => round(-$net, 2)];
            } elseif ($account['account_type'] === ACCOUNT_EXPENSE) {
                $expenses[] = $row + ['amount' => round($net, 2)];
            }
        }

        $totalRevenues = round(array_sum(array_column($revenues, 'amount')), 2);
        $totalExpenses = round(array_sum(array_column($expenses, 'amount')), 2);

        return [
            'start_date' => $start ?? '',
            'end_date' => $end ?? '',
            'revenues' => $revenues,
            'expenses' => $expenses,
            'total_revenues' => $totalRevenues,
            'total_expenses' => $totalExpenses,
            'net_result' => round($totalRevenues - $totalExpenses, 2)
        ];
    }

    /**
     * Comptes actifs accessibles à l'utilisateur (pour les listes de sélection).
     */
    public function accounts(): array
    {
        $scope = $this->companyScope('a.company_id');
        if ($scope === null) {
            return [];
        }

        $statement = $this->db->prepare(
            "SELECT a.id, a.account_number, a.name, c.name AS company_name
             FROM accounts a
             INNER JOIN companies c ON c.id = a.company_id
             WHERE a.is_active = 1 {$scope['sql']}
             ORDER BY a.account_number ASC, c.name ASC"
        );
        $statement->execute($scope['params']);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $companies = array_unique(array_column($rows, 'company_name'));
        if (count($companies) > 1) {
            foreach ($rows as &$row) {
                $row['name'] .= ' — ' . $row['company_name'];
            }
            unset($row);
        }

        return $rows;
    }

    /** Un compte accessible à l'utilisateur, ou null. */
    public function account(int $accountId): ?array
    {
        foreach ($this->accounts() as $account) {
            if ((int) $account['id'] === $accountId) {
                return $account;
            }
        }

        return null;
    }

    /**
     * Mouvements d'écritures validées (grand livre), triés par compte puis par date.
     *
     * @return array<int, array<string, mixed>>
     */
    public function ledgerEntries(?int $accountId, ?string $start, ?string $end): array
    {
        $scope = $this->companyScope('a.company_id');
        if ($scope === null) {
            return [];
        }

        $sql = "
            SELECT
                e.entry_date,
                e.reference,
                j.name AS journal_name,
                a.account_number,
                COALESCE(NULLIF(el.description, ''), e.description) AS description,
                el.debit,
                el.credit
            FROM entry_lines el
            INNER JOIN entries e ON e.id = el.entry_id
            INNER JOIN journals j ON j.id = e.journal_id
            INNER JOIN accounts a ON a.id = el.account_id
            WHERE e.status = ? {$scope['sql']}
        ";
        $params = array_merge([ENTRY_VALIDATED], $scope['params']);

        if ($accountId !== null) {
            $sql .= ' AND el.account_id = ?';
            $params[] = $accountId;
        }
        if ($start !== null) {
            $sql .= ' AND e.entry_date >= ?';
            $params[] = $start;
        }
        if ($end !== null) {
            $sql .= ' AND e.entry_date <= ?';
            $params[] = $end;
        }

        $sql .= ' ORDER BY a.account_number ASC, e.entry_date ASC, el.id ASC LIMIT ' . self::MAX_LEDGER_ROWS;

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Grand livre d'un compte avec solde progressif et report des écritures antérieures.
     *
     * @return array{entries: array<int, array<string, mixed>>, total_debit: float, total_credit: float, final_balance: float}
     */
    public function accountLedger(int $accountId, ?string $start, ?string $end): array
    {
        $opening = 0.0;

        if ($start !== null) {
            $before = $this->db->prepare(
                'SELECT COALESCE(SUM(el.debit), 0) AS d, COALESCE(SUM(el.credit), 0) AS c
                 FROM entry_lines el
                 INNER JOIN entries e ON e.id = el.entry_id
                 WHERE el.account_id = ? AND e.status = ? AND e.entry_date < ?'
            );
            $before->execute([$accountId, ENTRY_VALIDATED, $start]);
            $row = $before->fetch(PDO::FETCH_ASSOC);
            $opening = round((float) $row['d'] - (float) $row['c'], 2);
        }

        $entries = [];
        $balance = $opening;

        if ($opening != 0.0) {
            $entries[] = [
                'entry_date' => null,
                'reference' => '-',
                'journal_name' => '-',
                'description' => 'Report des écritures antérieures',
                'debit' => $opening > 0 ? $opening : 0.0,
                'credit' => $opening < 0 ? -$opening : 0.0,
                'balance' => $opening
            ];
        }

        $totalDebit = $opening > 0 ? $opening : 0.0;
        $totalCredit = $opening < 0 ? -$opening : 0.0;

        foreach ($this->ledgerEntries($accountId, $start, $end) as $row) {
            $debit = (float) $row['debit'];
            $credit = (float) $row['credit'];
            $balance = round($balance + $debit - $credit, 2);
            $totalDebit += $debit;
            $totalCredit += $credit;

            $row['balance'] = $balance;
            $entries[] = $row;
        }

        return [
            'entries' => $entries,
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'final_balance' => round($balance, 2)
        ];
    }

    /**
     * Restriction aux entreprises accessibles.
     *
     * @return array{sql: string, params: int[]}|null  null = aucun accès
     */
    private function companyScope(string $column): ?array
    {
        $companyIds = Auth::companyIds();

        if ($companyIds === null) {
            return ['sql' => '', 'params' => []];
        }

        $companyIds = array_values(array_map('intval', $companyIds));
        if ($companyIds === []) {
            return null;
        }

        return [
            'sql' => ' AND ' . $column . ' IN (' . implode(', ', array_fill(0, count($companyIds), '?')) . ')',
            'params' => $companyIds
        ];
    }
}
