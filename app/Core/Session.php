<?php

namespace App\Core;

class Session
{
    /**
     * Démarrer la session.
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {

            session_name(SESSION_NAME);

            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path' => '/',
                'secure' => SESSION_SECURE,
                'httponly' => SESSION_HTTP_ONLY,
                'samesite' => SESSION_SAME_SITE
            ]);

            ini_set('session.use_strict_mode', '1');
            ini_set('session.gc_maxlifetime', (string) SESSION_LIFETIME);

            // Sessions en base de données (hébergement serverless : Vercel, etc.).
            if (strtolower((string) app_env('SESSION_DRIVER', 'files')) === 'database') {
                try {
                    session_set_save_handler(new DatabaseSessionHandler(Database::connect()), true);
                } catch (\Throwable $exception) {
                    error_log('Session en base indisponible : ' . $exception->getMessage());
                }
            }

            session_start();
        }
    }

    /**
     * Enregistrer une donnée en session.
     */
    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Récupérer une donnée de session.
     */
    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Récupérer puis supprimer un message de session.
     */
    public static function getFlash(string $key, $default = null)
    {
        if (!self::has($key)) {
            return $default;
        }

        $value = self::get($key);
        self::remove($key);

        return $value;
    }

    /**
     * Vérifier l'existence d'une donnée.
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Supprimer une donnée de session.
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Détruire complètement la session.
     */
    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {

            $_SESSION = [];

            if (ini_get('session.use_cookies')) {

                $params = session_get_cookie_params();

                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }

            session_destroy();
        }
    }
}
