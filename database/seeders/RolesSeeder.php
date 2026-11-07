<?php

namespace Database\Seeders;

use PDO;

class RolesSeeder
{
    /**
     * Insérer les rôles principaux dans la base de données.
     */
    public function run(PDO $pdo): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Administrateur général du cabinet comptable.'
            ],
            [
                'name' => 'expert',
                'description' => 'Expert-comptable chargé du suivi et de la supervision comptable.'
            ],
            [
                'name' => 'accountant',
                'description' => 'Comptable chargé de la saisie et du suivi des opérations comptables.'
            ],
            [
                'name' => 'client',
                'description' => 'Client ayant accès aux informations de son entreprise.'
            ]
        ];

        $sql = "
            INSERT INTO roles (name, description)
            VALUES (:name, :description)
        " . (DB_DRIVER === 'pgsql'
            ? "
            ON CONFLICT (name) DO UPDATE SET
                description = EXCLUDED.description"
            : "
            ON DUPLICATE KEY UPDATE
                description = VALUES(description)") . "
        ";

        $statement = $pdo->prepare($sql);

        foreach ($roles as $role) {
            $statement->execute([
                'name' => $role['name'],
                'description' => $role['description']
            ]);
        }
    }
}
