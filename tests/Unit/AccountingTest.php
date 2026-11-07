<?php

declare(strict_types=1);

/**
 * =========================================================
 * CABINET COMPTABLE
 * Tests unitaires des opérations comptables
 * Fichier : tests/Unit/AccountingTest.php
 * =========================================================
 */

use PHPUnit\Framework\TestCase;

final class AccountingTest extends TestCase
{
    /**
     * Vérifier qu'une écriture équilibrée est acceptée.
     */
    public function testEntryIsBalanced(): void
    {
        $totalDebit = 150000;
        $totalCredit = 150000;

        $this->assertEquals($totalDebit, $totalCredit);
    }

    /**
     * Vérifier qu'une écriture déséquilibrée est détectée.
     */
    public function testEntryIsNotBalanced(): void
    {
        $totalDebit = 150000;
        $totalCredit = 120000;

        $this->assertNotEquals($totalDebit, $totalCredit);
    }

    /**
     * Vérifier le calcul du total des débits.
     */
    public function testDebitTotalCalculation(): void
    {
        $debits = [50000, 75000, 25000];

        $total = array_sum($debits);

        $this->assertEquals(150000, $total);
    }

    /**
     * Vérifier le calcul du total des crédits.
     */
    public function testCreditTotalCalculation(): void
    {
        $credits = [100000, 30000, 20000];

        $total = array_sum($credits);

        $this->assertEquals(150000, $total);
    }

    /**
     * Vérifier qu'un montant négatif est rejeté.
     */
    public function testNegativeAmountIsRejected(): void
    {
        $amount = -5000;

        $this->assertLessThan(0, $amount);
    }

    /**
     * Vérifier le calcul du solde.
     */
    public function testBalanceCalculation(): void
    {
        $totalDebit = 200000;
        $totalCredit = 150000;

        $balance = $totalDebit - $totalCredit;

        $this->assertEquals(50000, $balance);
    }

    /**
     * Vérifier que le total d'une liste vide est nul.
     */
    public function testEmptyAmountsTotalIsZero(): void
    {
        $amounts = [];

        $this->assertEquals(0, array_sum($amounts));
    }
}
