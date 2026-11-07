<?php

namespace Database\Seeders;

use PDO;

class AccountsSeeder
{
    /**
     * Insérer les comptes comptables pour chaque entreprise.
     */
    public function run(PDO $pdo): void
    {
        // Principaux comptes du plan comptable.
        $accounts = [
            [
                'number' => '101',
                'name' => 'Capital social',
                'type' => 'equity'
            ],
            [
                'number' => '401',
                'name' => 'Fournisseurs',
                'type' => 'liability'
            ],
            [
                'number' => '411',
                'name' => 'Clients',
                'type' => 'asset'
            ],
            [
                'number' => '443',
                'name' => 'TVA facturée',
                'type' => 'liability'
            ],
            [
                'number' => '445',
                'name' => 'TVA récupérable',
                'type' => 'asset'
            ],
            [
                'number' => '521',
                'name' => 'Banques',
                'type' => 'asset'
            ],
            [
                'number' => '571',
                'name' => 'Caisse',
                'type' => 'asset'
            ],
            [
                'number' => '601',
                'name' => 'Achats de marchandises',
                'type' => 'expense'
            ],
            [
                'number' => '701',
                'name' => 'Ventes de marchandises',
                'type' => 'revenue'
            ],
            [
                'number' => '661',
                'name' => 'Rémunérations du personnel',
                'type' => 'expense'
            ],
            [
                'number' => '681',
                'name' => 'Dotations aux amortissements',
                'type' => 'expense'
            ]
        ];

        // Récupérer les entreprises existantes.
        $companyStatement = $pdo->query("
            SELECT id
            FROM companies
        ");

        $companies = $companyStatement->fetchAll(PDO::FETCH_ASSOC);

        // Préparer l'insertion des comptes.
        $sql = "
            INSERT INTO accounts (
                company_id,
                account_number,
                name,
                type,
                is_active,
                created_at,
                updated_at
            ) VALUES (
                :company_id,
                :account_number,
                :name,
                :type,
                1,
                NOW(),
                NOW()
            )
            ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                type = VALUES(type),
                is_active = 1,
                updated_at = NOW()
        ";

        $statement = $pdo->prepare($sql);

        foreach ($companies as $company) {
            foreach ($accounts as $account) {
                $statement->execute([
                    'company_id' => $company['id'],
                    'account_number' => $account['number'],
                    'name' => $account['name'],
                    'type' => $account['type']
                ]);
            }
        }
    }
}
