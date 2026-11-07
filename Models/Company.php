<?php

namespace App\Models;

use App\Core\Model;

class Company extends Model
{
    /**
     * Récupérer toutes les entreprises.
     */
    public static function getAll(): array
    {
        $sql = "
            SELECT
                c.*,
                COUNT(DISTINCT uc.user_id) AS users_count
            FROM companies c
            LEFT JOIN user_companies uc ON uc.company_id = c.id
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ";

        return self::query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Rechercher une entreprise par son identifiant.
     */
    public static function findById(int $id): ?array
    {
        $sql = "
            SELECT *
            FROM companies
            WHERE id = :id
            LIMIT 1
        ";

        $result = self::query($sql, [
            'id' => $id
        ]);

        return $result->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Rechercher les entreprises d'un utilisateur.
     */
    public static function getByUser(int $userId): array
    {
        $sql = "
            SELECT c.*
            FROM companies c
            INNER JOIN user_companies uc
                ON uc.company_id = c.id
            WHERE uc.user_id = :user_id
            ORDER BY c.name ASC
        ";

        return self::query($sql, [
            'user_id' => $userId
        ])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Créer une entreprise.
     */
    public static function create(array $data): int|false
    {
        $sql = "
            INSERT INTO companies (
                name,
                email,
                phone,
                address,
                city,
                tax_number,
                registration_number,
                created_at,
                updated_at
            ) VALUES (
                :name,
                :email,
                :phone,
                :address,
                :city,
                :tax_number,
                :registration_number,
                NOW(),
                NOW()
            )
        ";

        $success = self::execute($sql, [
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'tax_number' => $data['tax_number'] ?? null,
            'registration_number' => $data['registration_number'] ?? null
        ]);

        if (!$success) {
            return false;
        }

        return (int) self::lastInsertId();
    }

    /**
     * Modifier une entreprise.
     */
    public static function update(int $id, array $data): bool
    {
        $sql = "
            UPDATE companies
            SET
                name = :name,
                email = :email,
                phone = :phone,
                address = :address,
                city = :city,
                tax_number = :tax_number,
                registration_number = :registration_number,
                updated_at = NOW()
            WHERE id = :id
        ";

        return self::execute($sql, [
            'id' => $id,
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'tax_number' => $data['tax_number'] ?? null,
            'registration_number' => $data['registration_number'] ?? null
        ]);
    }

    /**
     * Supprimer une entreprise.
     */
    public static function delete(int $id): bool
    {
        $sql = "
            DELETE FROM companies
            WHERE id = :id
        ";

        return self::execute($sql, [
            'id' => $id
        ]);
    }

    /**
     * Associer un utilisateur à une entreprise.
     */
    public static function assignUser(int $companyId, int $userId): bool
    {
        $sql = "
            INSERT INTO user_companies (
                user_id,
                company_id,
                created_at
            ) VALUES (
                :user_id,
                :company_id,
                NOW()
            )
        ";

        return self::execute($sql, [
            'user_id' => $userId,
            'company_id' => $companyId
        ]);
    }

    /**
     * Retirer l'accès d'un utilisateur à une entreprise.
     */
    public static function removeUser(int $companyId, int $userId): bool
    {
        $sql = "
            DELETE FROM user_companies
            WHERE company_id = :company_id
            AND user_id = :user_id
        ";

        return self::execute($sql, [
            'company_id' => $companyId,
            'user_id' => $userId
        ]);
    }

    /**
     * Compter les entreprises enregistrées.
     */
    public static function countAll(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM companies";

        $result = self::query($sql);

        return (int) $result->fetchColumn();
    }
}