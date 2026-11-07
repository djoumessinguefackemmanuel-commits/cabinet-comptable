<?php

namespace App\Models;

use App\Core\Model;

class Journal extends Model
{
    /**
     * Récupérer tous les journaux comptables.
     */
    public static function getAll(): array
    {
        $sql = "
            SELECT
                j.*,
                c.name AS company_name
            FROM journals j
            INNER JOIN companies c ON c.id = j.company_id
            ORDER BY j.code ASC
        ";

        return self::query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer tous les journaux actifs.
     */
    public static function getAllActive(): array
    {
        $sql = "
            SELECT
                j.*,
                c.name AS company_name
            FROM journals j
            INNER JOIN companies c ON c.id = j.company_id
            WHERE j.is_active = 1
            ORDER BY j.code ASC
        ";

        return self::query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getByCompanies(array $companyIds, bool $activeOnly = false): array
    {
        if ($companyIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($companyIds), '?'));
        $sql = "
            SELECT j.*, c.name AS company_name
            FROM journals j
            INNER JOIN companies c ON c.id = j.company_id
            WHERE j.company_id IN ({$placeholders})
        ";

        if ($activeOnly) {
            $sql .= ' AND j.is_active = 1';
        }

        $sql .= ' ORDER BY j.code ASC';

        return self::query($sql, $companyIds)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les journaux d'une entreprise.
     */
    public static function getByCompany(int $companyId): array
    {
        $sql = "
            SELECT *
            FROM journals
            WHERE company_id = :company_id
            ORDER BY code ASC
        ";

        return self::query($sql, [
            'company_id' => $companyId
        ])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer uniquement les journaux actifs.
     */
    public static function getActiveByCompany(int $companyId): array
    {
        $sql = "
            SELECT *
            FROM journals
            WHERE company_id = :company_id
            AND is_active = 1
            ORDER BY code ASC
        ";

        return self::query($sql, [
            'company_id' => $companyId
        ])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Rechercher un journal par son identifiant.
     */
    public static function findById(int $id): ?array
    {
        $sql = "
            SELECT *
            FROM journals
            WHERE id = :id
            LIMIT 1
        ";

        $result = self::query($sql, [
            'id' => $id
        ]);

        return $result->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Rechercher un journal par son code dans une entreprise.
     */
    public static function findByCode(
        int $companyId,
        string $code
    ): ?array {
        $sql = "
            SELECT *
            FROM journals
            WHERE company_id = :company_id
            AND code = :code
            LIMIT 1
        ";

        $result = self::query($sql, [
            'company_id' => $companyId,
            'code' => $code
        ]);

        return $result->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Créer un journal comptable.
     */
    public static function create(array $data): int|false
    {
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
                :is_active,
                NOW(),
                NOW()
            )
        ";

        $success = self::execute($sql, [
            'company_id' => $data['company_id'],
            'code' => $data['code'],
            'name' => $data['name'],
            'type' => $data['type'],
            'is_active' => $data['is_active'] ?? 1
        ]);

        if (!$success) {
            return false;
        }

        return (int) self::lastInsertId();
    }

    /**
     * Modifier un journal comptable.
     */
    public static function update(int $id, array $data): bool
    {
        $sql = "
            UPDATE journals
            SET
                code = :code,
                name = :name,
                type = :type,
                updated_at = NOW()
            WHERE id = :id
        ";

        return self::execute($sql, [
            'id' => $id,
            'code' => $data['code'],
            'name' => $data['name'],
            'type' => $data['type']
        ]);
    }

    /**
     * Activer ou désactiver un journal.
     */
    public static function updateStatus(int $id, int $isActive): bool
    {
        $sql = "
            UPDATE journals
            SET
                is_active = :is_active,
                updated_at = NOW()
            WHERE id = :id
        ";

        return self::execute($sql, [
            'id' => $id,
            'is_active' => $isActive ? 1 : 0
        ]);
    }

    /**
     * Supprimer un journal.
     */
    public static function delete(int $id): bool
    {
        $sql = "
            DELETE FROM journals
            WHERE id = :id
        ";

        return self::execute($sql, [
            'id' => $id
        ]);
    }

    /**
     * Compter les journaux d'une entreprise.
     */
    public static function countByCompany(int $companyId): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM journals
            WHERE company_id = :company_id
        ";

        $result = self::query($sql, [
            'company_id' => $companyId
        ]);

        return (int) $result->fetchColumn();
    }
}