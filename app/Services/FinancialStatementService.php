<?php

namespace App\Services;

use App\Core\Database;

class FinancialStatementService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Récupérer les soldes des comptes d'un exercice.
     */
    private function getAccountBalances(int $exerciseId): array
    {
        $sql = "
            SELECT
                a.id,
                a.account_number,
                a.name,
                a.type,
                COALESCE(SUM(el.debit), 0) AS total_debit,
                COALESCE(SUM(el.credit), 0) AS total_credit
            FROM accounts a
            INNER JOIN entry_lines el ON el.account_id = a.id
            INNER JOIN entries e ON e.id = el.entry_id
            WHERE e.exercise_id = :exercise_id
              AND e.status = :status
            GROUP BY a.id, a.account_number, a.name, a.type
            ORDER BY a.account_number ASC
        ";

        $statement = $this->db->prepare($sql);

        $statement->execute([
            'exercise_id' => $exerciseId,
            'status' => ENTRY_VALIDATED
        ]);

        return $statement->fetchAll();
    }

    /**
     * Préparer les données du bilan comptable.
     */
    public function getBalanceSheet(int $exerciseId): array
    {
        $accounts = $this->getAccountBalances($exerciseId);

        $assets = [];
        $liabilities = [];
        $equity = [];

        foreach ($accounts as $account) {

            $debit = (float) $account['total_debit'];
            $credit = (float) $account['total_credit'];

            $balance = $debit - $credit;

            $account['balance'] = round(abs($balance), 2);

            switch ($account['type']) {

                case ACCOUNT_ASSET:
                    $assets[] = $account;
                    break;

                case ACCOUNT_LIABILITY:
                    $liabilities[] = $account;
                    break;

                case ACCOUNT_EQUITY:
                    $equity[] = $account;
                    break;
            }
        }

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'total_assets' => $this->sumBalances($assets),
            'total_liabilities' => $this->sumBalances($liabilities),
            'total_equity' => $this->sumBalances($equity)
        ];
    }

    /**
     * Préparer les données du compte de résultat.
     */
    public function getIncomeStatement(int $exerciseId): array
    {
        $accounts = $this->getAccountBalances($exerciseId);

        $expenses = [];
        $revenues = [];

        foreach ($accounts as $account) {

            $debit = (float) $account['total_debit'];
            $credit = (float) $account['total_credit'];

            if ($account['type'] === ACCOUNT_EXPENSE) {

                $account['balance'] = round($debit - $credit, 2);

                $expenses[] = $account;
            }

            if ($account['type'] === ACCOUNT_REVENUE) {

                $account['balance'] = round($credit - $debit, 2);

                $revenues[] = $account;
            }
        }

        $totalExpenses = $this->sumBalances($expenses);

        $totalRevenues = $this->sumBalances($revenues);

        return [
            'expenses' => $expenses,
            'revenues' => $revenues,
            'total_expenses' => $totalExpenses,
            'total_revenues' => $totalRevenues,
            'result' => round(
                $totalRevenues - $totalExpenses,
                2
            )
        ];
    }

    /**
     * Calculer la somme des soldes d'une liste de comptes.
     */
    private function sumBalances(array $accounts): float
    {
        $total = 0;

        foreach ($accounts as $account) {
            $total += (float) $account['balance'];
        }

        return round($total, 2);
    }
}
