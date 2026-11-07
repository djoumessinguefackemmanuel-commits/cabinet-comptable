<?php

namespace Database\Seeders;

use PDO;

class JournalsSeeder
{
    /**
     * Insérer les journaux comptables pour chaque entreprise.
     */
    public function run(PDO $pdo): void
    {
        $journals = [
            [
                'code' => 'AC',
                'name' => 'Journal des achats',
                'type' => 'purchase'
            ],
            [
                'code' => 'VE',
                'name' => 'Journal des ventes',
                'type' => 'sales'
            ],
            [
                'code' => 'BQ',
                'name' => 'Journal de banque',
                'type' => 'bank'
            ],
            [
                'code' => 'CA',
                'name' => 'Journal de caisse',
                'type' => 'cash'
            ],
            [
                'code' => 'OD',
                'name' => 'Journal des opérations diverses',
                'type' => 'general'
            ]
        ];

        // Récupérer les entreprises existantes.
        $companyStatement = $pdo->query("
            SELECT id
            FROM companies
        ");

        $companies = $companyStatement->fetchAll(PDO::FETCH_ASSOC);

        // Préparer la requête d'insertion.
        $sql = "
            INSERT INTO journals (
                company_id,
                code,
                name,
                type,
                is_active,
                created_at,
                updated_at
            ) VALUES (
                :company_id,
                :code,
                :name,
                :type,
                1,
                NOW(),
                NOW()
            )
        " . (DB_DRIVER === 'pgsql'
            ? "
            ON CONFLICT (company_id, code) DO UPDATE SET
                name = EXCLUDED.name,
                type = EXCLUDED.type,
                is_active = 1,
                updated_at = NOW()"
            : "
            ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                type = VALUES(type),
                is_active = 1,
                updated_at = NOW()") . "
        ";

        $statement = $pdo->prepare($sql);

        foreach ($companies as $company) {
            foreach ($journals as $journal) {
                $statement->execute([
                    'company_id' => $company['id'],
                    'code' => $journal['code'],
                    'name' => $journal['name'],
                    'type' => $journal['type']
                ]);
            }
        }
    }
}
