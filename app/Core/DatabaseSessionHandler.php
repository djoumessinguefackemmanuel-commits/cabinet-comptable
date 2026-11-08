<?php

namespace App\Core;

use PDO;
use SessionHandlerInterface;
use SessionUpdateTimestampHandlerInterface;
use Throwable;

/**
 * Stockage des sessions PHP dans la base de données.
 *
 * Nécessaire sur les hébergements « serverless » (ex. Vercel) où chaque requête
 * peut être servie par un nouveau conteneur : les sessions stockées dans des
 * fichiers locaux y disparaissent d'une requête à l'autre (erreur 419).
 * Activé avec SESSION_DRIVER=database. La table `sessions` est créée au besoin.
 */
class DatabaseSessionHandler implements SessionHandlerInterface, SessionUpdateTimestampHandlerInterface
{
    private static bool $tableReady = false;

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->ensureTable();
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $statement = $this->pdo->prepare('SELECT payload FROM app_sessions WHERE id = ? AND expires_at > ?');
        $statement->execute([$id, time()]);
        $payload = $statement->fetchColumn();

        if ($payload === false) {
            return '';
        }

        $decoded = base64_decode((string) $payload, true);

        return $decoded === false ? '' : $decoded;
    }

    public function write(string $id, string $data): bool
    {
        $payload = base64_encode($data);
        $expiresAt = time() + (int) ini_get('session.gc_maxlifetime');

        if ($this->isPostgres()) {
            $sql = 'INSERT INTO app_sessions (id, payload, expires_at) VALUES (?, ?, ?)
                    ON CONFLICT (id) DO UPDATE SET payload = EXCLUDED.payload, expires_at = EXCLUDED.expires_at';
        } else {
            $sql = 'INSERT INTO app_sessions (id, payload, expires_at) VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE payload = VALUES(payload), expires_at = VALUES(expires_at)';
        }

        return $this->pdo->prepare($sql)->execute([$id, $payload, $expiresAt]);
    }

    public function destroy(string $id): bool
    {
        return $this->pdo->prepare('DELETE FROM app_sessions WHERE id = ?')->execute([$id]);
    }

    public function gc(int $max_lifetime): int|false
    {
        $statement = $this->pdo->prepare('DELETE FROM app_sessions WHERE expires_at < ?');
        $statement->execute([time()]);

        return $statement->rowCount();
    }

    public function validateId(string $id): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM app_sessions WHERE id = ? AND expires_at > ?');
        $statement->execute([$id, time()]);

        return $statement->fetchColumn() !== false;
    }

    public function updateTimestamp(string $id, string $data): bool
    {
        return $this->write($id, $data);
    }

    private function isPostgres(): bool
    {
        return defined('DB_DRIVER') && DB_DRIVER === 'pgsql';
    }

    private function ensureTable(): void
    {
        if (self::$tableReady) {
            return;
        }

        try {
            if ($this->isPostgres()) {
                $this->pdo->exec(
                    'CREATE TABLE IF NOT EXISTS app_sessions (
                        id VARCHAR(128) PRIMARY KEY,
                        payload TEXT NOT NULL,
                        expires_at BIGINT NOT NULL
                    )'
                );
                $this->pdo->exec(
                    'CREATE INDEX IF NOT EXISTS idx_app_sessions_expires ON app_sessions (expires_at)'
                );
            } else {
                $this->pdo->exec(
                    'CREATE TABLE IF NOT EXISTS app_sessions (
                        id VARCHAR(128) NOT NULL PRIMARY KEY,
                        payload MEDIUMTEXT NOT NULL,
                        expires_at BIGINT NOT NULL,
                        INDEX idx_app_sessions_expires (expires_at)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
                );
            }
        } catch (Throwable $exception) {
            error_log('DatabaseSessionHandler : ' . $exception->getMessage());
        }

        self::$tableReady = true;
    }
}
