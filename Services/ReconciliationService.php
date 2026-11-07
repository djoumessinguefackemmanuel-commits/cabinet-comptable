<?php

namespace App\Services;

class ReconciliationService
{
    /**
     * Comparer les opérations bancaires et comptables.
     */
    public function reconcile(
        array $bankTransactions,
        array $accountingTransactions
    ): array {

        $matched = [];
        $unmatchedBank = [];
        $unmatchedAccounting = [];

        $usedAccounting = [];

        foreach ($bankTransactions as $bankTransaction) {

            $found = false;

            foreach ($accountingTransactions as $index => $accountingTransaction) {

                if (isset($usedAccounting[$index])) {
                    continue;
                }

                if ($this->isMatching(
                    $bankTransaction,
                    $accountingTransaction
                )) {

                    $matched[] = [
                        'bank_transaction' => $bankTransaction,
                        'accounting_transaction' => $accountingTransaction
                    ];

                    $usedAccounting[$index] = true;

                    $found = true;

                    break;
                }
            }

            if (!$found) {
                $unmatchedBank[] = $bankTransaction;
            }
        }

        foreach ($accountingTransactions as $index => $transaction) {

            if (!isset($usedAccounting[$index])) {
                $unmatchedAccounting[] = $transaction;
            }
        }

        return [
            'matched' => $matched,
            'unmatched_bank' => $unmatchedBank,
            'unmatched_accounting' => $unmatchedAccounting,
            'matched_count' => count($matched),
            'unmatched_bank_count' => count($unmatchedBank),
            'unmatched_accounting_count' => count($unmatchedAccounting)
        ];
    }

    /**
     * Vérifier si deux opérations correspondent.
     */
    private function isMatching(
        array $bankTransaction,
        array $accountingTransaction
    ): bool {

        $bankAmount = round(
            (float) ($bankTransaction['amount'] ?? 0),
            2
        );

        $accountingAmount = round(
            (float) ($accountingTransaction['amount'] ?? 0),
            2
        );

        $bankDate = $bankTransaction['date'] ?? null;

        $accountingDate = $accountingTransaction['date'] ?? null;

        if ($bankAmount !== $accountingAmount) {
            return false;
        }

        if (!$bankDate || !$accountingDate) {
            return false;
        }

        return $bankDate === $accountingDate;
    }

    /**
     * Calculer les totaux des opérations non rapprochées.
     */
    public function calculateUnmatchedTotals(array $reconciliation): array
    {
        $bankTotal = 0;
        $accountingTotal = 0;

        foreach ($reconciliation['unmatched_bank'] ?? [] as $transaction) {

            $bankTotal += (float) ($transaction['amount'] ?? 0);
        }

        foreach ($reconciliation['unmatched_accounting'] ?? [] as $transaction) {

            $accountingTotal += (float) ($transaction['amount'] ?? 0);
        }

        return [
            'unmatched_bank_total' => round($bankTotal, 2),
            'unmatched_accounting_total' => round($accountingTotal, 2)
        ];
    }
}
