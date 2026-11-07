<?php

namespace App\Services;

use App\Core\Database;

class NotificationService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Créer une notification pour un utilisateur.
     */
    public function create(
        int $userId,
        string $title,
        string $message,
        string $type = 'info'
    ): bool {

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

        $statement = $this->db->prepare($sql);

        return $statement->execute([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'status' => NOTIFICATION_UNREAD
        ]);
    }

    /**
     * Récupérer les notifications d'un utilisateur.
     */
    public function getByUser(int $userId): array
    {
        $sql = "
            SELECT *
            FROM notifications
            WHERE user_id = :user_id
            ORDER BY created_at DESC
        ";

        $statement = $this->db->prepare($sql);

        $statement->execute([
            'user_id' => $userId
        ]);

        return $statement->fetchAll();
    }

    /**
     * Récupérer le nombre de notifications non lues.
     */
    public function getUnreadCount(int $userId): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM notifications
            WHERE user_id = :user_id
              AND status = :status
        ";

        $statement = $this->db->prepare($sql);

        $statement->execute([
            'user_id' => $userId,
            'status' => NOTIFICATION_UNREAD
        ]);

        return (int) $statement->fetchColumn();
    }

    /**
     * Marquer une notification comme lue.
     */
    public function markAsRead(
        int $notificationId,
        int $userId
    ): bool {

        $sql = "
            UPDATE notifications
            SET status = :status
            WHERE id = :id
              AND user_id = :user_id
        ";

        $statement = $this->db->prepare($sql);

        return $statement->execute([
            'status' => NOTIFICATION_READ,
            'id' => $notificationId,
            'user_id' => $userId
        ]);
    }

    /**
     * Marquer toutes les notifications comme lues.
     */
    public function markAllAsRead(int $userId): bool
    {
        $sql = "
            UPDATE notifications
            SET status = :status
            WHERE user_id = :user_id
              AND status = :unread_status
        ";

        $statement = $this->db->prepare($sql);

        return $statement->execute([
            'status' => NOTIFICATION_READ,
            'user_id' => $userId,
            'unread_status' => NOTIFICATION_UNREAD
        ]);
    }
}
