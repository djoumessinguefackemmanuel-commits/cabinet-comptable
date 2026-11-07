<?php

namespace App\Models;

use App\Core\Model;

class AuditLog extends Model
{
    /**
     * Récupérer les journaux d'audit récents.
     */
    public static function getRecent(int $limit = 50): array
    {
        $limit = max(1, min($limit, 500));

        $sql = "
            SELECT
                al.*,
                u.first_name,
                u.last_name,
                u.email
            FROM audit_logs al
            LEFT JOIN users u ON u.id = al.user_id
            ORDER BY al.created_at DESC
            LIMIT {$limit}
        ";

        return self::query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les journaux d'audit d'un utilisateur.
     */
    public static function getByUser(int $userId): array
    {
        $sql = "
            SELECT
                al.*,
                u.first_name,
                u.last_name,
                u.email
            FROM audit_logs al
            LEFT JOIN users u ON u.id = al.user_id
            WHERE al.user_id = :user_id
            ORDER BY al.created_at DESC
        ";

        return self::query($sql, [
            'user_id' => $userId
        ])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Rechercher un journal d'audit par son identifiant.
     */
    public static function findById(int $id): ?array
    {
        $sql = "
            SELECT
                al.*,
                u.first_name,
                u.last_name,
                u.email
            FROM audit_logs al
            LEFT JOIN users u ON u.id = al.user_id
            WHERE al.id = :id
            LIMIT 1
        ";

        $result = self::query($sql, [
            'id' => $id
        ]);

        return $result->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Enregistrer une action dans le journal d'audit.
     */
    public static function create(array $data): int|false
    {
        $sql = "
            INSERT INTO audit_logs (
                user_id,
                action,
                entity,
                entity_id,
                description,
                ip_address,
                user_agent,
                created_at
            ) VALUES (
                :user_id,
                :action,
                :entity,
                :entity_id,
                :description,
                :ip_address,
                :user_agent,
                NOW()
            )
        ";

        $success = self::execute($sql, [
            'user_id' => $data['user_id'] ?? null,
            'action' => $data['action'],
            'entity' => $data['entity'] ?? null,
            'entity_id' => $data['entity_id'] ?? null,
            'description' => $data['description'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null
        ]);

        if (!$success) {
            return false;
        }

        return (int) self::lastInsertId();
    }

    /**
     * Supprimer un journal d'audit.
     */
    public static function delete(int $id): bool
    {
        $sql = "
            DELETE FROM audit_logs
            WHERE id = :id
        ";

        return self::execute($sql, [
            'id' => $id
        ]);
    }

    /**
     * Compter le nombre total d'actions enregistrées.
     */
    public static function countAll(): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM audit_logs
        ";

        $result = self::query($sql);

        return (int) $result->fetchColumn();
    }
}