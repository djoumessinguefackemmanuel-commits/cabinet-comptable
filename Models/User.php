<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class User extends Model
{
    protected string $table = 'users';

    /**
     * Récupérer tous les utilisateurs.
     */
    public static function getAll(): array
    {
        $sql = "
            SELECT
                u.*,
                r.name AS role_name
            FROM users u
            LEFT JOIN roles r ON r.id = u.role_id
            ORDER BY u.created_at DESC
        ";

        return self::query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les utilisateurs selon leur rôle.
     */
    public static function getAllByRole(string $role): array
    {
        $sql = "
            SELECT
                u.*,
                r.name AS role_name
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            WHERE r.name = ?
            ORDER BY u.created_at DESC
        ";

        return self::query($sql, [$role])->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Rechercher un utilisateur par son identifiant.
     */
    public static function findById(int $id): ?array
    {
        $sql = "
            SELECT
                u.*,
                r.name AS role
            FROM users u
            LEFT JOIN roles r ON r.id = u.role_id
            WHERE u.id = ?
            LIMIT 1
        ";

        $user = self::query($sql, [$id])->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * Rechercher un utilisateur par son adresse e-mail.
     */
    public static function findByEmail(string $email): ?array
    {
        $sql = "
            SELECT
                u.*,
                r.name AS role
            FROM users u
            LEFT JOIN roles r ON r.id = u.role_id
            WHERE u.email = ?
            LIMIT 1
        ";

        $user = self::query($sql, [$email])->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * Créer un utilisateur.
     */
    public static function create(array $data): int|false
    {
        $sql = "
            INSERT INTO users (
                first_name,
                last_name,
                email,
                phone,
                password,
                role_id,
                status,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ";

        $password = password_hash($data['password'], PASSWORD_DEFAULT);

        $result = self::execute($sql, [
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'] ?? null,
            $password,
            $data['role_id'],
            $data['status'] ?? USER_ACTIVE
        ]);

        return $result ? (int) self::lastInsertId() : false;
    }

    /**
     * Modifier les informations d'un utilisateur.
     */
    public static function update(int $id, array $data): bool
    {
        $sql = "
            UPDATE users
            SET
                first_name = ?,
                last_name = ?,
                email = ?,
                phone = ?,
                role_id = ?,
                status = ?,
                updated_at = NOW()
            WHERE id = ?
        ";

        return self::execute($sql, [
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'] ?? null,
            $data['role_id'],
            $data['status'] ?? USER_ACTIVE,
            $id
        ]);
    }

    /**
     * Modifier le mot de passe d'un utilisateur.
     */
    public static function updatePassword(int $id, string $password): bool
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        return self::execute(
            "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?",
            [$hashedPassword, $id]
        );
    }

    /**
     * Modifier le statut d'un utilisateur.
     */
    public static function updateStatus(int $id, string $status): bool
    {
        return self::execute(
            "UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?",
            [$status, $id]
        );
    }

    /**
     * Supprimer un utilisateur.
     */
    public static function delete(int $id): bool
    {
        return self::execute(
            "DELETE FROM users WHERE id = ?",
            [$id]
        );
    }

    /**
     * Vérifier si une adresse e-mail existe déjà.
     */
    public static function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
        $params = [$email];

        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        return (int) self::query($sql, $params)->fetchColumn() > 0;
    }

    /**
     * Compter les utilisateurs.
     */
    public static function countAll(): int
    {
        return (int) self::query(
            "SELECT COUNT(*) FROM users"
        )->fetchColumn();
    }

    /**
     * Compter les utilisateurs selon leur statut.
     */
    public static function countByStatus(string $status): int
    {
        return (int) self::query(
            "SELECT COUNT(*) FROM users WHERE status = ?",
            [$status]
        )->fetchColumn();
    }
}