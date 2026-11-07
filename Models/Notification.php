<?php

namespace App\Models;

use App\Core\Model;

class Notification extends Model
{
    /**
     * Récupérer toutes les notifications d'un utilisateur.
     */
    public static function getByUser(int $userId): array
    {
        $sql = "
            SELECT *
            FROM notifications
            WHERE user_id = :user_id
            ORDER BY created_at DESC
        ";

        return self::query($sql, [
            'user_id' => $userId
        ])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer une notification par son identifiant.
     */
    public static function findById(int $id): ?array
    {
        $sql = "
            SELECT *
            FROM notifications
            WHERE id = :id
            LIMIT 1
        ";

        $result = self::query($sql, [
            'id' => $id
        ]);

        return $result->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Créer une notification.
     */
    public static function create(array $data): int|false
    {
        $sql = "
            INSERT INTO notifications (
                user_id,
                title,
                message,
                type,
                status,
                created_at
            ) VALUES (
                :user_id,
                :title,
                :message,
                :type,
                :status,
                NOW()
            )
        ";

        $success = self::execute($sql, [
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'message' => $data['message'],
            'type' => $data['type'] ?? 'info',
            'status' => $data['status'] ?? NOTIFICATION_UNREAD
        ]);

        if (!$success) {
            return false;
        }

        return (int) self::lastInsertId();
    }

    /**
     * Compter les notifications non lues d'un utilisateur.
     */
    public static function getUnreadCount(int $userId): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM notifications
            WHERE user_id = :user_id
            AND status = :status
        ";

        $result = self::query($sql, [
            'user_id' => $userId,
            'status' => NOTIFICATION_UNREAD
        ]);

        return (int) $result->fetchColumn();
    }

    /**
     * Marquer une notification comme lue.
     */
    public static function markAsRead(int $id, int $userId): bool
    {
        $sql = "
            UPDATE notifications
            SET status = :status
            WHERE id = :id
            AND user_id = :user_id
        ";

        return self::execute($sql, [
            'id' => $id,
            'user_id' => $userId,
            'status' => NOTIFICATION_READ
        ]);
    }

    /**
     * Marquer toutes les notifications d'un utilisateur comme lues.
     */
    public static function markAllAsRead(int $userId): bool
    {
        $sql = "
            UPDATE notifications
            SET status = :status
            WHERE user_id = :user_id
            AND status = :current_status
        ";

        return self::execute($sql, [
            'user_id' => $userId,
            'status' => NOTIFICATION_READ,
            'current_status' => NOTIFICATION_UNREAD
        ]);
    }

    /**
     * Supprimer une notification.
     */
    public static function delete(int $id, int $userId): bool
    {
        $sql = "
            DELETE FROM notifications
            WHERE id = :id
            AND user_id = :user_id
        ";

        return self::execute($sql, [
            'id' => $id,
            'user_id' => $userId
        ]);
    }

    /**
     * Compter toutes les notifications d'un utilisateur.
     */
    public static function countByUser(int $userId): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM notifications
            WHERE user_id = :user_id
        ";

        $result = self::query($sql, [
            'user_id' => $userId
        ]);

        return (int) $result->fetchColumn();
    }
}