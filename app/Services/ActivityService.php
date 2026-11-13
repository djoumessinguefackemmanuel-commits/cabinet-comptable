<?php

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * Historique d'activité personnel affiché dans le profil.
 *
 * Supprimer une ligne la MASQUE seulement pour l'utilisateur : le journal d'audit (audit_logs),
 * qui sert de preuve, reste intact et consultable par les administrateurs.
 */
class ActivityService
{
    private static bool $tableReady = false;

    /** @return array<int,array{id:int|string,action:string,description:?string,created_at:string}> */
    public static function visible(int $userId, int $limit = 10): array
    {
        try {
            $statement = self::pdo()->prepare(
                "SELECT l.id, l.action, l.description, l.created_at
                 FROM audit_logs l
                 WHERE l.user_id = ?
                   AND l.action <> 'activity_cleared'
                   AND NOT EXISTS (SELECT 1 FROM user_activity_hidden h WHERE h.user_id = l.user_id AND h.log_id = l.id)
                 ORDER BY l.created_at DESC, l.id DESC
                 LIMIT " . (int) $limit
            );
            $statement->execute([$userId]);

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $exception) {
            error_log('ActivityService::visible : ' . $exception->getMessage());
            return [];
        }
    }

    /** Masquer une ligne (uniquement si elle appartient bien à l'utilisateur). */
    public static function hide(int $userId, int $logId): void
    {
        $pdo = self::pdo();
        $pdo->prepare(
            "INSERT INTO user_activity_hidden (user_id, log_id)
             SELECT l.user_id, l.id FROM audit_logs l
             WHERE l.id = ? AND l.user_id = ?
               AND NOT EXISTS (SELECT 1 FROM user_activity_hidden h WHERE h.user_id = l.user_id AND h.log_id = l.id)"
        )->execute([$logId, $userId]);
    }

    /** Masquer tout l'historique de l'utilisateur. */
    public static function hideAll(int $userId): void
    {
        self::pdo()->prepare(
            "INSERT INTO user_activity_hidden (user_id, log_id)
             SELECT l.user_id, l.id FROM audit_logs l
             WHERE l.user_id = ?
               AND NOT EXISTS (SELECT 1 FROM user_activity_hidden h WHERE h.user_id = l.user_id AND h.log_id = l.id)"
        )->execute([$userId]);
    }

    private static function pdo(): PDO
    {
        $pdo = Database::connect();

        if (!self::$tableReady) {
            if (defined('DB_DRIVER') && DB_DRIVER === 'pgsql') {
                $pdo->exec(
                    'CREATE TABLE IF NOT EXISTS user_activity_hidden (
                        user_id INTEGER NOT NULL,
                        log_id BIGINT NOT NULL,
                        PRIMARY KEY (user_id, log_id)
                    )'
                );
            } else {
                $pdo->exec(
                    'CREATE TABLE IF NOT EXISTS user_activity_hidden (
                        user_id BIGINT UNSIGNED NOT NULL,
                        log_id BIGINT UNSIGNED NOT NULL,
                        PRIMARY KEY (user_id, log_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
                );
            }
            self::$tableReady = true;
        }

        return $pdo;
    }
}
