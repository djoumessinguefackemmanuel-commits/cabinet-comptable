<?php
namespace App\Services;

use App\Core\Database;

class BalanceService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Récupérer les mouvements comptables regroupés par compte.
     */
    public function getBalance(int $exerciseId): array
    {
        $sql = "
            SELECT
                a.id AS account_id,
                a.account_number AS account_number,
                a.name AS account_name,
                COALESCE(SUM(el.debit), 0) AS total_debit,
                COALESCE(SUM(el.credit), 0) AS total_credit
            FROM accounts a
            LEFT JOIN (
                entry_lines el
                INNER JOIN entries e
                    ON e.id = el.entry_id
                    AND e.exercise_id = :exercise_id
                    AND e.status = :status
            ) ON el.account_id = a.id
            WHERE a.is_active = 1
            GROUP BY a.id, a.account_number, a.name
            ORDER BY a.account_number ASC
        ";

        $statement = $this->db->prepare($sql);

        $statement->execute([
            'exercise_id' => $exerciseId,
            'status' => ENTRY_VALIDATED
        ]);

        $accounts = $statement->fetchAll();

        foreach ($accounts as &$account) {

            $debit = (float) $account['total_debit'];
            $credit = (float) $account['total_credit'];

            $difference = $debit - $credit;

            $account['balance'] = round(abs($difference), 2);

            $account['balance_type'] = $difference > 0
                ? 'debiteur'
                : ($difference < 0 ? 'crediteur' : 'solde');

            $account['total_debit'] = round($debit, 2);
            $account['total_credit'] = round($credit, 2);
        }

        unset($account);

        return $accounts;
    }

    /**
     * Calculer les totaux généraux de la balance.
     */
    public function getTotals(int $exerciseId): array
    {
        $sql = "
            SELECT
                COALESCE(SUM(el.debit), 0) AS total_debit,
                COALESCE(SUM(el.credit), 0) AS total_credit
            FROM entry_lines el
            INNER JOIN entries e ON e.id = el.entry_id
            WHERE e.exercise_id = :exercise_id
              AND e.status = :status
        ";

        $statement = $this->db->prepare($sql);

        $statement->execute([
            'exercise_id' => $exerciseId,
            'status' => ENTRY_VALIDATED
        ]);

        $totals = $statement->fetch();

        return [
            'total_debit' => round((float) $totals['total_debit'], 2),
            'total_credit' => round((float) $totals['total_credit'], 2),
            'balanced' => abs(
                (float) $totals['total_debit']
                - (float) $totals['total_credit']
            ) < 0.01
        ];
    }
}
