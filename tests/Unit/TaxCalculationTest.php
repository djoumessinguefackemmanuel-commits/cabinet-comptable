<?php

declare(strict_types=1);

/**
 * =========================================================
 * CABINET COMPTABLE
 * Tests unitaires des calculs fiscaux
 * Fichier : tests/Unit/TaxCalculationTest.php
 * =========================================================
 */

use PHPUnit\Framework\TestCase;

final class TaxCalculationTest extends TestCase
{
    /**
     * Calculer le montant de la TVA.
     */
    public function testVatCalculation(): void
    {
        $amountHT = 100000;
        $vatRate = 19.25;

        $vatAmount = $amountHT * ($vatRate / 100);

        $this->assertEqualsWithDelta(
            19250,
            $vatAmount,
            0.01
        );
    }

    /**
     * Calculer le montant TTC.
     */
    public function testTotalIncludingVat(): void
    {
        $amountHT = 100000;
        $vatAmount = 19250;

        $amountTTC = $amountHT + $vatAmount;

        $this->assertEquals(119250, $amountTTC);
    }

    /**
     * Calculer le montant HT à partir du TTC.
     */
    public function testAmountExcludingVat(): void
    {
        $amountTTC = 119250;
        $vatRate = 19.25;

        $amountHT = $amountTTC / (1 + ($vatRate / 100));

        $this->assertEqualsWithDelta(
            100000,
            $amountHT,
            0.01
        );
    }

    /**
     * Vérifier qu'un taux de TVA ne peut pas être négatif.
     */
    public function testNegativeVatRateIsInvalid(): void
    {
        $vatRate = -5;

        $this->assertLessThan(0, $vatRate);
    }

    /**
     * Vérifier qu'un montant nul produit une TVA nulle.
     */
    public function testZeroAmountProducesZeroVat(): void
    {
        $amountHT = 0;
        $vatRate = 19.25;

        $vatAmount = $amountHT * ($vatRate / 100);

        $this->assertEquals(0, $vatAmount);
    }

    /**
     * Vérifier le calcul d'une taxe de 5 %.
     */
    public function testCustomTaxRateCalculation(): void
    {
        $amount = 200000;
        $taxRate = 5;

        $taxAmount = $amount * ($taxRate / 100);

        $this->assertEquals(10000, $taxAmount);
    }

    /**
     * Vérifier que le montant TTC est supérieur au montant HT
     * lorsque le taux de taxe est positif.
     */
    public function testTotalIsGreaterThanNetAmount(): void
    {
        $amountHT = 50000;
        $taxRate = 19.25;

        $amountTTC = $amountHT + (
            $amountHT * ($taxRate / 100)
        );

        $this->assertGreaterThan($amountHT, $amountTTC);
    }
}
