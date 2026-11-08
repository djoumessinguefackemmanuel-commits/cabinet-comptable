<?php

namespace App\Services;

use App\Core\Database;
use PDO;
use Throwable;

/**
 * Limitation des tentatives de connexion (protection contre la force brute).
 *
 * Les adresses e-mail et IP ne sont jamais stockées en clair : seuls leurs
 * empreintes SHA-256 sont enregistrées. La table est créée automatiquement
 * au premier usage (voir aussi database/migrations/017_create_login_attempts.sql).
 */
class LoginThrottle
{
    /** Fenêtre d'observation, en secondes (15 minutes). */
    private const WINDOW_SECONDS = 900;

    /** Échecs tolérés pour un même compte depuis une même adresse IP. */
    private const MAX_PER_ACCOUNT_AND_IP = 5;

    /** Échecs tolérés pour un même compte, toutes adresses IP confondues. */
    private const MAX_PER_ACCOUNT = 20;

    /** Échecs tolérés depuis une même adresse IP, tous comptes confondus. */
    private const MAX_PER_IP = 40;

    private static bool $tableReady = false;

    /**
     * Indiquer si de nouvelles tentatives doivent être refusées pour le moment.
     */
    public static function isBlocked(string $email): bool
    {
        try {
            $pdo = self::pdo();
            $emailHash = self::hash(strtolower(trim($email)));
            $ipHash = self::hash(self::clientIp());
            $since = self::cutoff();

            return self::count($pdo, 'email_hash = ? AND ip_hash = ?', [$emailHash, $ipHash, $since]) >= self::MAX_PER_ACCOUNT_AND_IP
                || self::count($pdo, 'email_hash = ?', [$emailHash, $since]) >= self::MAX_PER_ACCOUNT
                || self::count($pdo, 'ip_hash = ?', [$ipHash, $since]) >= self::MAX_PER_IP;
        } catch (Throwable $exception) {
            // En cas de panne de la base, la connexion échouera de toute façon.
            error_log('LoginThrottle::isBlocked : ' . $exception->getMessage());
            return false;
        }
    }

    /**
     * Enregistrer une tentative de connexion échouée.
     */
    public static function recordFailure(string $email): void
    {
        try {
            $pdo = self::pdo();
            $statement = $pdo->prepare(
                'INSERT INTO login_attempts (email_hash, ip_hash, attempted_at) VALUES (?, ?, ?)'
            );
            $statement->execute([
                self::hash(strtolower(trim($email))),
                self::hash(self::clientIp()),
                gmdate('Y-m-d H:i:s')
            ]);

            // Nettoyage occasionnel des anciennes tentatives.
            if (random_int(1, 50) === 1) {
                $pdo->prepare('DELETE FROM login_attempts WHERE attempted_at < ?')
                    ->execute([gmdate('Y-m-d H:i:s', time() - 86400)]);
            }
        } catch (Throwable $exception) {
            error_log('LoginThrottle::recordFailure : ' . $exception->getMessage());
        }
    }

    /**
     * Effacer les échecs d'un compte après une connexion réussie.
     */
    public static function clear(string $email): void
    {
        try {
            self::pdo()
                ->prepare('DELETE FROM login_attempts WHERE email_hash = ? AND ip_hash = ?')
                ->execute([
                    self::hash(strtolower(trim($email))),
                    self::hash(self::clientIp())
                ]);
        } catch (Throwable $exception) {
            error_log('LoginThrottle::clear : ' . $exception->getMessage());
        }
    }

    private static function count(PDO $pdo, string $condition, array $params): int
    {
        $statement = $pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts WHERE ' . $condition . ' AND attempted_at > ?'
        );
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    private static function cutoff(): string
    {
        return gmdate('Y-m-d H:i:s', time() - self::WINDOW_SECONDS);
    }

    private static function hash(string $value): string
    {
        return hash('sha256', $value);
    }

    /**
     * Adresse IP du visiteur. Derrière le proxy de l'hébergeur, REMOTE_ADDR est
     * celle du proxy : on utilise alors X-Real-IP, posé par ce proxy.
     */
    private static function clientIp(): string
    {
        $real = $_SERVER['HTTP_X_REAL_IP'] ?? '';
        if (is_string($real) && filter_var($real, FILTER_VALIDATE_IP) !== false) {
            return $real;
        }

        return (string) ($_SERVER['REMOTE_ADDR'] ?? 'inconnue');
    }

    private static function pdo(): PDO
    {
        $pdo = Database::connect();

        if (!self::$tableReady) {
            if (defined('DB_DRIVER') && DB_DRIVER === 'pgsql') {
                $pdo->exec(
                    'CREATE TABLE IF NOT EXISTS login_attempts (
                        id BIGSERIAL PRIMARY KEY,
                        email_hash CHAR(64) NOT NULL,
                        ip_hash CHAR(64) NOT NULL,
                        attempted_at TIMESTAMP NOT NULL
                    )'
                );
                $pdo->exec(
                    'CREATE INDEX IF NOT EXISTS idx_login_attempts_email
                     ON login_attempts (email_hash, attempted_at)'
                );
                $pdo->exec(
                    'CREATE INDEX IF NOT EXISTS idx_login_attempts_ip
                     ON login_attempts (ip_hash, attempted_at)'
                );
            } else {
                $pdo->exec(
                    'CREATE TABLE IF NOT EXISTS login_attempts (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        email_hash CHAR(64) NOT NULL,
                        ip_hash CHAR(64) NOT NULL,
                        attempted_at DATETIME NOT NULL,
                        INDEX idx_login_attempts_email (email_hash, attempted_at),
                        INDEX idx_login_attempts_ip (ip_hash, attempted_at)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
                );
            }

            self::$tableReady = true;
        }

        return $pdo;
    }
}
