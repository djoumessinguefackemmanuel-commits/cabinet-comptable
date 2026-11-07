<?php

namespace Database\Seeders;

use PDO;
use RuntimeException;

class AdminSeeder
{
    /**
     * Créer le premier compte administrateur.
     */
    public function run(PDO $pdo): void
    {
        // Rechercher le rôle administrateur.
        $roleStatement = $pdo->prepare("
            SELECT id
            FROM roles
            WHERE name = :name
            LIMIT 1
        ");

        $roleStatement->execute([
            'name' => 'admin'
        ]);

        $role = $roleStatement->fetch(PDO::FETCH_ASSOC);

        if (!$role) {
            throw new RuntimeException(
                "Le rôle administrateur n'existe pas. Exécutez RolesSeeder d'abord."
            );
        }

        // Vérifier si le compte administrateur existe déjà.
        $email = 'admin@cabinet-comptable.local';

        $userStatement = $pdo->prepare("
            SELECT id
            FROM users
            WHERE email = :email
            LIMIT 1
        ");

        $userStatement->execute([
            'email' => $email
        ]);

        if ($userStatement->fetch(PDO::FETCH_ASSOC)) {
            return;
        }

        // Créer le compte administrateur.
        $password = password_hash(
            'Admin@12345',
            PASSWORD_DEFAULT
        );

        $insertStatement = $pdo->prepare("
            INSERT INTO users (
                role_id,
                first_name,
                last_name,
                email,
                phone,
                password,
                status,
                created_at,
                updated_at
            ) VALUES (
                :role_id,
                :first_name,
                :last_name,
                :email,
                :phone,
                :password,
                :status,
                NOW(),
                NOW()
            )
        ");

        $insertStatement->execute([
            'role_id' => $role['id'],
            'first_name' => 'Administrateur',
            'last_name' => 'Principal',
            'email' => $email,
            'phone' => null,
            'password' => $password,
            'status' => 'active'
        ]);
    }
}
