<?php

namespace App\Services;

use App\Core\Database;

class LedgerService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Récupérer les mouvements d'un compte comptable.
     */
    public function getAccountMovements(
        int $accountId,
        int $exerciseId
    ): array {

        $sql = "
            SELECT
                el.id,
                el.entry_id,
                el.account_id,
                el.description,
                el.debit,
                el.credit,
                e.entry_date AS entry_date,
                e.reference AS entry_reference,
                j.code AS journal_code,
                j.name AS journal_name
            FROM entry_lines el
            INNER JOIN entries e ON e.id = el.entry_id
            INNER JOIN journals j ON j.id = e.journal_id
            WHERE el.account_id = :account_id
              AND e.exercise_id = :exercise_id
              AND e.status = :status
            ORDER BY e.entry_date ASC, el.id ASC
        ";

        $statement = $this->db->prepare($sql);

        $statement->execute([
            'account_id' => $accountId,
            'exercise_id' => $exerciseId,
            'status' => ENTRY_VALIDATED
        ]);

        return $statement->fetchAll();
    }

    /**
     * Calculer le solde d'un compte à partir de ses mouvements.
     */
    public function calculateBalance(array $movements): array
    {
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($movements as $movement) {

            $totalDebit += (float) $movement['debit'];

            $totalCredit += (float) $movement['credit'];
        }

        $balance = $totalDebit - $totalCredit;

        return [
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'balance' => round(abs($balance), 2),
            'balance_type' => $balance > 0
                ? 'debiteur'
                : ($balance < 0 ? 'crediteur' : 'solde')
        ];
    }

    /**
     * Récupérer le grand livre d'un compte.
     */
    public function getLedger(
        int $accountId,
        int $exerciseId
    ): array {

        $movements = $this->getAccountMovements(
            $accountId,
            $exerciseId
        );

        return [
            'movements' => $movements,
            'totals' => $this->calculateBalance($movements)
        ];
    }
}
