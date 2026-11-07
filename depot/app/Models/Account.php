<?php

namespace App\Models;

use App\Core\Model;

class Account extends Model
{
    /**
     * Récupérer tous les comptes comptables.
     */
    public static function getAll(): array
    {
        $sql = "
            SELECT
                a.*,
                c.name AS company_name
            FROM accounts a
            INNER JOIN companies c ON c.id = a.company_id
            ORDER BY a.account_number ASC
        ";

        return self::query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer tous les comptes actifs.
     */
    public static function getAllActive(): array
    {
        $sql = "
            SELECT
                a.*,
                c.name AS company_name
            FROM accounts a
            INNER JOIN companies c ON c.id = a.company_id
            WHERE a.is_active = 1
            ORDER BY a.account_number ASC
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
            SELECT a.*, c.name AS company_name
            FROM accounts a
            INNER JOIN companies c ON c.id = a.company_id
            WHERE a.company_id IN ({$placeholders})
        ";

        if ($activeOnly) {
            $sql .= ' AND a.is_active = 1';
        }

        $sql .= ' ORDER BY a.account_number ASC';

        return self::query($sql, $companyIds)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les comptes d'une entreprise.
     */
    public static function getByCompany(int $companyId): array
    {
        $sql = "
            SELECT *
            FROM accounts
            WHERE company_id = :company_id
            ORDER BY account_number ASC
        ";

        return self::query($sql, [
            'company_id' => $companyId
        ])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les comptes actifs d'une entreprise.
     */
    public static function getActiveByCompany(int $companyId): array
    {
        $sql = "
            SELECT *
            FROM accounts
            WHERE company_id = :company_id
            AND is_active = 1
            ORDER BY account_number ASC
        ";

        return self::query($sql, [
            'company_id' => $companyId
        ])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Rechercher un compte par son identifiant.
     */
    public static function findById(int $id): ?array
    {
        $sql = "
            SELECT *
            FROM accounts
            WHERE id = :id
            LIMIT 1
        ";

        $result = self::query($sql, [
            'id' => $id
        ]);

        return $result->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Rechercher un compte par son numéro dans une entreprise.
     */
    public static function findByNumber(
        int $companyId,
        string $accountNumber
    ): ?array {
        $sql = "
            SELECT *
            FROM accounts
            WHERE company_id = :company_id
            AND account_number = :account_number
            LIMIT 1
        ";

        $result = self::query($sql, [
            'company_id' => $companyId,
            'account_number' => $accountNumber
        ]);

        return $result->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Créer un compte comptable.
     */
    public static function create(array $data): int|false
    {
        $sql = "
            INSERT INTO accounts (
                company_id,
                account_number,
                name,
                type,
                description,
                is_active,
                created_at,
                updated_at
            ) VALUES (
                :company_id,
                :account_number,
                :name,
                :type,
                :description,
                :is_active,
                NOW(),
                NOW()
            )
        ";

        $success = self::execute($sql, [
            'company_id' => $data['company_id'],
            'account_number' => $data['account_number'],
            'name' => $data['name'],
            'type' => $data['type'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? 1
        ]);

        if (!$success) {
            return false;
        }

        return (int) self::lastInsertId();
    }

    /**
     * Modifier un compte comptable.
     */
    public static function update(int $id, array $data): bool
    {
        $sql = "
            UPDATE accounts
            SET
                account_number = :account_number,
                name = :name,
                type = :type,
                description = :description,
                updated_at = NOW()
            WHERE id = :id
        ";

        return self::execute($sql, [
            'id' => $id,
            'account_number' => $data['account_number'],
            'name' => $data['name'],
            'type' => $data['type'],
            'description' => $data['description'] ?? null
        ]);
    }

    /**
     * Modifier l'état actif ou inactif d'un compte.
     */
    public static function updateStatus(int $id, int $isActive): bool
    {
        $sql = "
            UPDATE accounts
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
     * Supprimer un compte comptable.
     */
    public static function delete(int $id): bool
    {
        $sql = "
            DELETE FROM accounts
            WHERE id = :id
        ";

        return self::execute($sql, [
            'id' => $id
        ]);
    }

    /**
     * Compter les comptes d'une entreprise.
     */
    public static function countByCompany(int $companyId): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM accounts
            WHERE company_id = :company_id
        ";

        $result = self::query($sql, [
            'company_id' => $companyId
        ]);

        return (int) $result->fetchColumn();
    }
}