<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Role extends Model
{
    /**
     * Récupérer tous les rôles.
     */
    public static function getAll(): array
    {
        $sql = "SELECT * FROM roles ORDER BY id ASC";

        return self::query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Rechercher un rôle par son identifiant.
     */
    public static function findById(int $id): ?array
    {
        $sql = "SELECT * FROM roles WHERE id = :id LIMIT 1";

        $result = self::query($sql, [
            'id' => $id
        ]);

        return $result->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Rechercher un rôle par son nom.
     */
    public static function findByName(string $name): ?array
    {
        $sql = "SELECT * FROM roles WHERE name = :name LIMIT 1";

        $result = self::query($sql, [
            'name' => $name
        ]);

        return $result->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Créer un nouveau rôle.
     */
    public static function create(array $data): int|false
    {
        $sql = "
            INSERT INTO roles (
                name,
                description,
                created_at
            ) VALUES (
                :name,
                :description,
                NOW()
            )
        ";

        $success = self::execute($sql, [
            'name' => $data['name'],
            'description' => $data['description'] ?? null
        ]);

        if (!$success) {
            return false;
        }

        return (int) self::lastInsertId();
    }

    /**
     * Modifier un rôle.
     */
    public static function update(int $id, array $data): bool
    {
        $sql = "
            UPDATE roles
            SET
                name = :name,
                description = :description
            WHERE id = :id
        ";

        return self::execute($sql, [
            'id' => $id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null
        ]);
    }

    /**
     * Affecter un rôle à un utilisateur.
     */
    public static function assignToUser(int $userId, int $roleId): bool
    {
        $sql = "
            UPDATE users
            SET role_id = :role_id
            WHERE id = :user_id
        ";

        return self::execute($sql, [
            'role_id' => $roleId,
            'user_id' => $userId
        ]);
    }

    /**
     * Supprimer un rôle.
     */
    public static function delete(int $id): bool
    {
        $sql = "DELETE FROM roles WHERE id = :id";

        return self::execute($sql, [
            'id' => $id
        ]);
    }

    /**
     * Compter les rôles enregistrés.
     */
    public static function countAll(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM roles";

        $result = self::query($sql);

        return (int) $result->fetchColumn();
    }
}