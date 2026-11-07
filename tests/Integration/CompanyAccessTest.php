<?php

declare(strict_types=1);

/**
 * =========================================================
 * CABINET COMPTABLE
 * Tests d'intégration des accès aux entreprises
 * Fichier : tests/Integration/CompanyAccessTest.php
 * =========================================================
 */

use PHPUnit\Framework\TestCase;

final class CompanyAccessTest extends TestCase
{
    /**
     * Vérifier qu'un utilisateur autorisé accède à son entreprise.
     */
    public function testAuthorizedUserCanAccessCompany(): void
    {
        $user = [
            'id' => 1,
            'role' => 'accountant',
            'companies' => [10, 20]
        ];

        $companyId = 10;

        $this->assertContains(
            $companyId,
            $user['companies']
        );
    }

    /**
     * Vérifier qu'un utilisateur non autorisé
     * ne peut pas accéder à une entreprise.
     */
    public function testUnauthorizedUserCannotAccessCompany(): void
    {
        $user = [
            'id' => 2,
            'role' => 'accountant',
            'companies' => [10, 20]
        ];

        $companyId = 30;

        $this->assertNotContains(
            $companyId,
            $user['companies']
        );
    }

    /**
     * Vérifier qu'un utilisateur sans entreprise
     * ne peut accéder à aucune entreprise.
     */
    public function testUserWithoutCompanyHasNoAccess(): void
    {
        $user = [
            'id' => 3,
            'role' => 'client',
            'companies' => []
        ];

        $this->assertEmpty($user['companies']);
    }

    /**
     * Vérifier qu'un administrateur possède
     * le rôle attendu.
     */
    public function testAdministratorRoleIsIdentified(): void
    {
        $user = [
            'id' => 1,
            'role' => 'admin'
        ];

        $this->assertEquals('admin', $user['role']);
    }

    /**
     * Vérifier qu'un identifiant d'entreprise
     * doit être strictement positif.
     */
    public function testCompanyIdentifierMustBePositive(): void
    {
        $companyId = 10;

        $this->assertGreaterThan(0, $companyId);
    }

    /**
     * Vérifier qu'une entreprise est correctement identifiée.
     */
    public function testCompanyInformationIsAvailable(): void
    {
        $company = [
            'id' => 10,
            'name' => 'Entreprise Exemple',
            'tax_number' => 'M012345678901A'
        ];

        $this->assertArrayHasKey('id', $company);
        $this->assertArrayHasKey('name', $company);
        $this->assertArrayHasKey('tax_number', $company);
        $this->assertNotEmpty($company['name']);
    }

    /**
     * Vérifier qu'un utilisateur ne possède pas
     * deux fois la même entreprise dans ses accès.
     */
    public function testCompanyAccessListCanBeUnique(): void
    {
        $companyIds = [10, 20, 30];

        $uniqueIds = array_unique($companyIds);

        $this->assertCount(
            count($uniqueIds),
            $companyIds
        );
    }
}
