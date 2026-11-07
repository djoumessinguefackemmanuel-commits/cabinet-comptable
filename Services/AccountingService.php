<?php

namespace App\Services;

class AccountingService
{
    /**
     * Calculer les totaux débit et crédit d'une écriture.
     */
    public function calculateTotals(array $lines): array
    {
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($lines as $line) {

            $totalDebit += (float) ($line['debit'] ?? 0);

            $totalCredit += (float) ($line['credit'] ?? 0);
        }

        return [
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'difference' => round(
                abs($totalDebit - $totalCredit),
                2
            )
        ];
    }

    /**
     * Vérifier l'équilibre d'une écriture comptable.
     */
    public function isBalanced(array $lines): bool
    {
        if (empty($lines)) {
            return false;
        }

        $totals = $this->calculateTotals($lines);

        return $totals['total_debit'] > 0
            && abs(
                $totals['total_debit'] - $totals['total_credit']
            ) < 0.01;
    }

    /**
     * Vérifier la validité des montants d'une ligne comptable.
     */
    public function isValidLine(array $line): bool
    {
        $debit = (float) ($line['debit'] ?? 0);

        $credit = (float) ($line['credit'] ?? 0);

        if ($debit < 0 || $credit < 0) {
            return false;
        }

        // Une ligne ne doit pas avoir simultanément un débit et un crédit.
        if ($debit > 0 && $credit > 0) {
            return false;
        }

        // Une ligne doit contenir un débit ou un crédit.
        return $debit > 0 || $credit > 0;
    }

    /**
     * Vérifier toutes les lignes d'une écriture.
     */
    public function validateLines(array $lines): bool
    {
        if (count($lines) < 2) {
            return false;
        }

        foreach ($lines as $line) {

            if (!$this->isValidLine($line)) {
                return false;
            }
        }

        return $this->isBalanced($lines);
    }
}
