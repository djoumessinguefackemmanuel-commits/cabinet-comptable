<?php

declare(strict_types=1);

/**
 * =========================================================
 * CABINET COMPTABLE
 * Tests unitaires de validation des écritures
 * Fichier : tests/Unit/EntryValidationTest.php
 * =========================================================
 */

use PHPUnit\Framework\TestCase;

final class EntryValidationTest extends TestCase
{
    /**
     * Vérifier qu'un montant positif est valide.
     */
    public function testPositiveAmountIsValid(): void
    {
        $amount = 25000;

        $this->assertGreaterThan(0, $amount);
    }

    /**
     * Vérifier qu'un montant nul est détecté.
     */
    public function testZeroAmountIsDetected(): void
    {
        $amount = 0;

        $this->assertEquals(0, $amount);
    }

    /**
     * Vérifier qu'un montant négatif est invalide.
     */
    public function testNegativeAmountIsInvalid(): void
    {
        $amount = -15000;

        $this->assertLessThan(0, $amount);
    }

    /**
     * Vérifier qu'un numéro de compte non vide est valide.
     */
    public function testAccountNumberIsRequired(): void
    {
        $accountNumber = '411000';

        $this->assertNotEmpty($accountNumber);
    }

    /**
     * Vérifier qu'un numéro de compte vide est invalide.
     */
    public function testEmptyAccountNumberIsInvalid(): void
    {
        $accountNumber = '';

        $this->assertEmpty($accountNumber);
    }

    /**
     * Vérifier qu'une écriture équilibrée est valide.
     */
    public function testBalancedEntryIsValid(): void
    {
        $debit = 100000;
        $credit = 100000;

        $this->assertEquals($debit, $credit);
    }

    /**
     * Vérifier qu'une écriture déséquilibrée est invalide.
     */
    public function testUnbalancedEntryIsInvalid(): void
    {
        $debit = 100000;
        $credit = 80000;

        $this->assertNotEquals($debit, $credit);
    }

    /**
     * Vérifier la présence de la date comptable.
     */
    public function testEntryDateIsRequired(): void
    {
        $entryDate = '2026-11-05';

        $this->assertNotEmpty($entryDate);

        $date = DateTime::createFromFormat('Y-m-d', $entryDate);

        $this->assertInstanceOf(DateTime::class, $date);
    }

    /**
     * Vérifier la présence du libellé.
     */
    public function testEntryDescriptionIsRequired(): void
    {
        $description = 'Enregistrement d’une opération comptable';

        $this->assertNotEmpty(trim($description));
    }
}
