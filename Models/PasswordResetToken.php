<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class PasswordResetToken extends Model
{
    public static function deleteForUser(int $userId): void
    {
        self::execute(
            'DELETE FROM password_reset_tokens WHERE user_id = ?',
            [$userId]
        );
    }

    public static function createForUser(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $db = Database::connect();
        $db->beginTransaction();

        try {
            self::execute(
                'DELETE FROM password_reset_tokens WHERE user_id = ?',
                [$userId]
            );
            self::execute(
                'INSERT INTO password_reset_tokens (user_id, token_hash, expires_at, created_at)
                 VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW())',
                [$userId, hash('sha256', $token)]
            );
            $db->commit();
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }

        return $token;
    }

    public static function resetPassword(string $tokenHash, string $password): bool
    {
        $db = Database::connect();
        $db->beginTransaction();

        try {
            $statement = $db->prepare(
                'SELECT user_id
                 FROM password_reset_tokens
                 WHERE token_hash = ? AND expires_at > NOW()
                 LIMIT 1
                 FOR UPDATE'
            );
            $statement->execute([$tokenHash]);
            $userId = $statement->fetchColumn();

            if ($userId === false) {
                $db->rollBack();
                return false;
            }

            $statement = $db->prepare(
                'UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?'
            );
            $statement->execute([password_hash($password, PASSWORD_DEFAULT), $userId]);

            $statement = $db->prepare(
                'DELETE FROM password_reset_tokens WHERE user_id = ?'
            );
            $statement->execute([$userId]);
            $db->commit();

            return true;
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }
    }
}
