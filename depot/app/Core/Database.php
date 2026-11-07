<?php
namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    /**
     * Établir la connexion à la base de données.
     */
    public static function connect(): PDO
    {
        if (self::$connection === null) {
            $dsn = 'mysql:host=' . DB_HOST
                 . ';port=' . DB_PORT
                 . ';dbname=' . DB_NAME
                 . ';charset=' . DB_CHARSET;

            try {
                self::$connection = new PDO(
                    $dsn,
                    DB_USER,
                    DB_PASS,
                    DB_OPTIONS
                );
            } catch (PDOException $exception) {
                if (APP_DEBUG) {
                    throw new PDOException(
                        'Erreur de connexion à la base de données : '
                        . $exception->getMessage()
                    );
                }

                error_log($exception->getMessage());

                throw new PDOException(
                    'Impossible de se connecter à la base de données.'
                );
            }
        }

        return self::$connection;
    }

    /**
     * Compatibilité avec les services qui utilisent getConnection().
     */
    public static function getConnection(): PDO
    {
        return self::connect();
    }

    /**
     * Empêcher l'instanciation directe.
     */
    private function __construct()
    {
    }

    /**
     * Empêcher le clonage.
     */
    private function __clone()
    {
    }
}
