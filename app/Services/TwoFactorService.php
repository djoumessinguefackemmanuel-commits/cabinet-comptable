<?php

namespace App\Services;

use App\Core\Database;
use PDO;
use RuntimeException;

/**
 * Double authentification (TOTP) : stockage chiffré du secret, codes de secours.
 *
 * Le secret est chiffré (AES-256-GCM) avec la clé APP_KEY : une fuite de la base
 * seule ne suffit pas à générer des codes. La table est créée automatiquement.
 */
class TwoFactorService
{
    private const RECOVERY_CODE_COUNT = 8;
    private const RECOVERY_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    private static bool $tableReady = false;

    /** La clé de chiffrement est-elle configurée ? */
    public static function isConfigured(): bool
    {
        return strlen((string) app_env('APP_KEY', '')) >= 16;
    }

    public static function isEnabled(int $userId): bool
    {
        try {
            $row = self::row($userId);
        } catch (\Throwable $exception) {
            error_log('TwoFactorService::isEnabled : ' . $exception->getMessage());
            // Mieux vaut bloquer la connexion que contourner la 2FA par erreur technique.
            throw $exception;
        }

        return $row !== null && (int) $row['enabled'] === 1;
    }

    public static function remainingRecoveryCodes(int $userId): int
    {
        $row = self::row($userId);
        if ($row === null || empty($row['recovery_codes'])) {
            return 0;
        }

        $codes = json_decode((string) $row['recovery_codes'], true);

        return is_array($codes) ? count($codes) : 0;
    }

    /**
     * Activer la 2FA si le code saisi correspond au secret en cours de configuration.
     *
     * @return string[]|null  codes de secours (affichés une seule fois), null si le code est faux
     */
    public static function enable(int $userId, string $secret, string $code): ?array
    {
        $step = Totp::verify($secret, $code);
        if ($step === null) {
            return null;
        }

        $recovery = self::generateRecoveryCodes();
        $pdo = self::pdo();

        $pdo->prepare('DELETE FROM user_two_factor WHERE user_id = :id')->execute(['id' => $userId]);
        $pdo->prepare(
            'INSERT INTO user_two_factor (user_id, secret_enc, enabled, recovery_codes, last_step)
             VALUES (:id, :secret, 1, :codes, :step)'
        )->execute([
            'id' => $userId,
            'secret' => self::encrypt($secret),
            'codes' => json_encode(array_map([self::class, 'hashRecoveryCode'], $recovery)),
            'step' => $step,
        ]);

        return $recovery;
    }

    /**
     * Vérifier le code saisi à la connexion : code de l'application ou code de secours.
     * Un code déjà utilisé est refusé.
     */
    public static function verifyLogin(int $userId, string $input): bool
    {
        $row = self::row($userId);
        if ($row === null || (int) $row['enabled'] !== 1) {
            return false;
        }

        $pdo = self::pdo();
        $normalized = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $input) ?? '');

        // Code à 6 chiffres de l'application d'authentification.
        if (preg_match('/^\d{6}$/', $normalized) === 1) {
            $step = Totp::verify(self::decrypt((string) $row['secret_enc']), $normalized, (int) $row['last_step']);
            if ($step === null) {
                return false;
            }

            // Mise à jour conditionnelle : deux requêtes simultanées ne peuvent pas réutiliser le même code.
            $statement = $pdo->prepare(
                'UPDATE user_two_factor SET last_step = :step WHERE user_id = :id AND last_step < :step'
            );
            $statement->execute(['step' => $step, 'id' => $userId]);

            return $statement->rowCount() === 1;
        }

        // Code de secours (usage unique).
        if (strlen($normalized) === 10) {
            $codes = json_decode((string) ($row['recovery_codes'] ?? '[]'), true);
            if (!is_array($codes)) {
                return false;
            }

            $hash = self::hashRecoveryCode($normalized);
            $index = array_search($hash, $codes, true);
            if ($index === false) {
                return false;
            }

            unset($codes[$index]);
            $statement = $pdo->prepare(
                'UPDATE user_two_factor SET recovery_codes = :new WHERE user_id = :id AND recovery_codes = :old'
            );
            $statement->execute([
                'new' => json_encode(array_values($codes)),
                'id' => $userId,
                'old' => $row['recovery_codes'],
            ]);

            return $statement->rowCount() === 1;
        }

        return false;
    }

    /** Remplacer les codes de secours ; retourne les nouveaux codes (affichés une seule fois). */
    public static function regenerateRecoveryCodes(int $userId): array
    {
        $recovery = self::generateRecoveryCodes();
        self::pdo()->prepare('UPDATE user_two_factor SET recovery_codes = :codes WHERE user_id = :id')->execute([
            'codes' => json_encode(array_map([self::class, 'hashRecoveryCode'], $recovery)),
            'id' => $userId,
        ]);

        return $recovery;
    }

    public static function disable(int $userId): void
    {
        self::pdo()->prepare('DELETE FROM user_two_factor WHERE user_id = :id')->execute(['id' => $userId]);
    }

    /** @return string[] */
    private static function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < self::RECOVERY_CODE_COUNT; $i++) {
            $code = '';
            for ($j = 0; $j < 10; $j++) {
                $code .= self::RECOVERY_ALPHABET[random_int(0, strlen(self::RECOVERY_ALPHABET) - 1)];
            }
            $codes[] = substr($code, 0, 5) . '-' . substr($code, 5);
        }

        return $codes;
    }

    private static function hashRecoveryCode(string $code): string
    {
        return hash('sha256', strtoupper(str_replace('-', '', $code)));
    }

    private static function key(): string
    {
        if (!self::isConfigured()) {
            throw new RuntimeException('APP_KEY n\'est pas configurée : la double authentification est indisponible.');
        }

        return hash('sha256', (string) app_env('APP_KEY'), true);
    }

    private static function encrypt(string $plain): string
    {
        $iv = random_bytes(12);
        $cipher = openssl_encrypt($plain, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($cipher === false) {
            throw new RuntimeException('Chiffrement impossible.');
        }

        return base64_encode($iv . $tag . $cipher);
    }

    private static function decrypt(string $payload): string
    {
        $raw = base64_decode($payload, true);
        if ($raw === false || strlen($raw) < 29) {
            throw new RuntimeException('Secret illisible.');
        }

        $plain = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));
        if ($plain === false) {
            throw new RuntimeException('Secret illisible (clé APP_KEY modifiée ?).');
        }

        return $plain;
    }

    private static function row(int $userId): ?array
    {
        $statement = self::pdo()->prepare('SELECT * FROM user_two_factor WHERE user_id = :id');
        $statement->execute(['id' => $userId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    private static function pdo(): PDO
    {
        $pdo = Database::connect();

        if (!self::$tableReady) {
            if (defined('DB_DRIVER') && DB_DRIVER === 'pgsql') {
                $pdo->exec(
                    'CREATE TABLE IF NOT EXISTS user_two_factor (
                        user_id INTEGER PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
                        secret_enc TEXT NOT NULL,
                        enabled SMALLINT NOT NULL DEFAULT 0,
                        recovery_codes TEXT NULL,
                        last_step BIGINT NOT NULL DEFAULT 0,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )'
                );
            } else {
                $pdo->exec(
                    'CREATE TABLE IF NOT EXISTS user_two_factor (
                        user_id INT UNSIGNED NOT NULL PRIMARY KEY,
                        secret_enc TEXT NOT NULL,
                        enabled TINYINT NOT NULL DEFAULT 0,
                        recovery_codes TEXT NULL,
                        last_step BIGINT NOT NULL DEFAULT 0,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        CONSTRAINT fk_user_two_factor_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
                );
            }

            self::$tableReady = true;
        }

        return $pdo;
    }
}
