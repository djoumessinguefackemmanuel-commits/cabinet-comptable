<?php

namespace App\Models;

use App\Core\Model;

class UserCompany extends Model
{
    /**
     * Récupérer toutes les associations utilisateurs-entreprises.
     */
    public static function getAll(): array
    {
        $sql = "
            SELECT
                uc.*,
                u.first_name,
                u.last_name,
                u.email,
                c.name AS company_name
            FROM user_companies uc
            INNER JOIN users u ON u.id = uc.user_id
            INNER JOIN companies c ON c.id = uc.company_id
            ORDER BY uc.created_at DESC
        ";

        return self::query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Vérifier si un utilisateur a accès à une entreprise.
     */
    public static function hasAccess(int $userId, int $companyId): bool
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM user_companies
            WHERE user_id = :user_id
            AND company_id = :company_id
        ";

        $result = self::query($sql, [
            'user_id' => $userId,
            'company_id' => $companyId
        ]);

        return (int) $result->fetchColumn() > 0;
    }

    /**
     * Récupérer les entreprises associées à un utilisateur.
     */
    public static function getCompaniesByUser(int $userId): array
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
     * Récupérer les identifiants des entreprises accessibles à un utilisateur.
     */
    public static function getCompanyIdsByUser(int $userId): array
    {
        $sql = "
            SELECT company_id
            FROM user_companies
            WHERE user_id = :user_id
        ";

        $ids = self::query($sql, [
            'user_id' => $userId
        ])->fetchAll(\PDO::FETCH_COLUMN);

        return array_map('intval', $ids);
    }

    /**
     * Récupérer les utilisateurs associés à une entreprise.
     */
    public static function getUsersByCompany(int $companyId): array
    {
        $sql = "
            SELECT
                u.id,
                u.first_name,
                u.last_name,
                u.email,
                u.phone,
                u.status
            FROM users u
            INNER JOIN user_companies uc
                ON uc.user_id = u.id
            WHERE uc.company_id = :company_id
            ORDER BY u.last_name ASC, u.first_name ASC
        ";

        return self::query($sql, [
            'company_id' => $companyId
        ])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Associer un utilisateur à une entreprise.
     */
    public static function create(int $userId, int $companyId): bool
    {
        if (self::hasAccess($userId, $companyId)) {
            return true;
        }

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

    public static function syncForUser(int $userId, array $companyIds): bool
    {
        $companyIds = array_values(array_unique(array_map('intval', $companyIds)));
        $db = \App\Core\Database::connect();
        $db->beginTransaction();

        try {
            if ($companyIds !== []) {
                $placeholders = implode(',', array_fill(0, count($companyIds), '?'));
                $statement = $db->prepare(
                    "SELECT COUNT(*) FROM companies WHERE id IN ({$placeholders})"
                );
                $statement->execute($companyIds);
                if ((int) $statement->fetchColumn() !== count($companyIds)) {
                    $db->rollBack();
                    return false;
                }
            }

            self::execute('DELETE FROM user_companies WHERE user_id = ?', [$userId]);
            foreach ($companyIds as $companyId) {
                self::execute(
                    'INSERT INTO user_companies (user_id, company_id, created_at) VALUES (?, ?, NOW())',
                    [$userId, $companyId]
                );
            }

            $db->commit();
            return true;
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }
    }

    /**
     * Supprimer une association.
     */
    public static function delete(int $userId, int $companyId): bool
    {
        $sql = "
            DELETE FROM user_companies
            WHERE user_id = :user_id
            AND company_id = :company_id
        ";

        return self::execute($sql, [
            'user_id' => $userId,
            'company_id' => $companyId
        ]);
    }

    /**
     * Compter les utilisateurs associés à une entreprise.
     */
    public static function countByCompany(int $companyId): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM user_companies
            WHERE company_id = :company_id
        ";

        $result = self::query($sql, [
            'company_id' => $companyId
        ]);

        return (int) $result->fetchColumn();
    }
}