<?php
namespace App\Services;

use App\Core\Auth;
use App\Core\Database;
use PDO;
use PDOException;

class AuditService
{
    /**
     * Enregistrer une action dans le journal d'audit.
     */
    public static function log(
        string $action,
        string $entity,
        ?int $entityId = null,
        ?string $description = null
    ): bool {
        try {
            $userId = Auth::id();

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

            $stmt = Database::getConnection()->prepare($sql);

            return $stmt->execute([
                ':user_id' => $userId,
                ':action' => $action,
                ':entity' => $entity,
                ':entity_id' => $entityId,
                ':description' => $description,
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);

        } catch (PDOException $e) {
            error_log('Erreur AuditService::log : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer les dernières actions enregistrées.
     */
    public static function getRecent(int $limit = 50): array
    {
        try {
            $limit = max(1, min($limit, 500));

            $sql = "
                SELECT
                    audit_logs.*,
                    users.first_name,
                    users.last_name,
                    users.email
                FROM audit_logs
                LEFT JOIN users ON users.id = audit_logs.user_id
                ORDER BY audit_logs.created_at DESC
                LIMIT :limit
            ";

            $stmt = Database::getConnection()->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log('Erreur AuditService::getRecent : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupérer les actions d'un utilisateur.
     */
    public static function getByUser(int $userId, int $limit = 50): array
    {
        try {
            $limit = max(1, min($limit, 500));

            $sql = "
                SELECT *
                FROM audit_logs
                WHERE user_id = :user_id
                ORDER BY created_at DESC
                LIMIT :limit
            ";

            $stmt = Database::getConnection()->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log('Erreur AuditService::getByUser : ' . $e->getMessage());
            return [];
        }
    }
}
