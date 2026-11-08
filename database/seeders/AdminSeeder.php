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
        $email = getenv('ADMIN_INITIAL_EMAIL') ?: 'admin@cabinet-comptable.local';

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

        // Créer le compte administrateur avec un mot de passe non prévisible :
        // celui de ADMIN_INITIAL_PASSWORD s'il est défini, sinon un mot de passe
        // aléatoire affiché une seule fois dans la console.
        $plainPassword = (string) getenv('ADMIN_INITIAL_PASSWORD');
        $generated = false;

        if ($plainPassword === '') {
            $alphabet = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            $plainPassword = '';
            for ($i = 0; $i < 16; $i++) {
                $plainPassword .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $generated = true;
        }

        if (strlen($plainPassword) < 12) {
            throw new RuntimeException(
                'ADMIN_INITIAL_PASSWORD doit contenir au moins 12 caractères.'
            );
        }

        $password = password_hash(
            $plainPassword,
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

        if ($generated) {
            fwrite(
                STDOUT,
                "Compte administrateur : {$email}\n"
                . "Mot de passe généré (affiché une seule fois, à changer après la première connexion) : {$plainPassword}\n"
            );
        }
    }
}
