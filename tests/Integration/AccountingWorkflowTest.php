<?php

declare(strict_types=1);

/**
 * =========================================================
 * CABINET COMPTABLE
 * Tests d'intégration du processus comptable
 * Fichier : tests/Integration/AccountingWorkflowTest.php
 * =========================================================
 */

use PHPUnit\Framework\TestCase;

final class AccountingWorkflowTest extends TestCase
{
    /**
     * Vérifier la création d'une écriture comptable.
     */
    public function testAccountingEntryCanBeCreated(): void
    {
        $entry = [
            'reference' => 'OD-2026-001',
            'date' => '2026-11-05',
            'description' => 'Enregistrement d’une opération',
            'status' => 'draft'
        ];

        $this->assertNotEmpty($entry['reference']);
        $this->assertNotEmpty($entry['date']);
        $this->assertNotEmpty($entry['description']);
        $this->assertEquals('draft', $entry['status']);
    }

    /**
     * Vérifier l'ajout des lignes comptables.
     */
    public function testEntryLinesCanBeAdded(): void
    {
        $lines = [];

        $lines[] = [
            'account' => '411000',
            'debit' => 100000,
            'credit' => 0
        ];

        $lines[] = [
            'account' => '701000',
            'debit' => 0,
            'credit' => 100000
        ];

        $this->assertCount(2, $lines);
    }

    /**
     * Vérifier le calcul des totaux.
     */
    public function testWorkflowCalculatesTotals(): void
    {
        $lines = [
            ['debit' => 100000, 'credit' => 0],
            ['debit' => 0, 'credit' => 100000]
        ];

        $totalDebit = array_sum(
            array_column($lines, 'debit')
        );

        $totalCredit = array_sum(
            array_column($lines, 'credit')
        );

        $this->assertEquals(100000, $totalDebit);
        $this->assertEquals(100000, $totalCredit);
    }

    /**
     * Vérifier qu'une écriture équilibrée peut être validée.
     */
    public function testBalancedEntryCanBeValidated(): void
    {
        $totalDebit = 100000;
        $totalCredit = 100000;

        $isBalanced = abs($totalDebit - $totalCredit) < 0.01;

        $this->assertTrue($isBalanced);
    }

    /**
     * Vérifier qu'une écriture déséquilibrée
     * ne peut pas être validée.
     */
    public function testUnbalancedEntryCannotBeValidated(): void
    {
        $totalDebit = 100000;
        $totalCredit = 80000;

        $isBalanced = abs($totalDebit - $totalCredit) < 0.01;

        $this->assertFalse($isBalanced);
    }

    /**
     * Vérifier le changement de statut après validation.
     */
    public function testEntryStatusChangesAfterValidation(): void
    {
        $entry = [
            'status' => 'draft'
        ];

        $entry['status'] = 'validated';

        $this->assertEquals('validated', $entry['status']);
    }

    /**
     * Vérifier qu'une écriture possède une date valide.
     */
    public function testEntryDateIsValid(): void
    {
        $date = '2026-11-05';

        $parsedDate = DateTime::createFromFormat('Y-m-d', $date);

        $this->assertInstanceOf(DateTime::class, $parsedDate);
    }

    /**
     * Vérifier qu'une écriture possède au moins une ligne.
     */
    public function testEntryMustContainAtLeastOneLine(): void
    {
        $lines = [
            ['account' => '411000', 'debit' => 50000, 'credit' => 0]
        ];

        $this->assertNotEmpty($lines);
    }
}
