<?php

namespace App\Services;

use App\Core\Database;
use PDO;
use RuntimeException;

/**
 * Stockage des fichiers téléversés.
 *
 * - STORAGE_DRIVER=local (défaut) : dossier storage/documents du serveur.
 * - STORAGE_DRIVER=database : contenu enregistré dans la base (table document_files).
 *   À utiliser sur les hébergements dont le disque est éphémère (Vercel, Railway…),
 *   où les fichiers locaux disparaissent à chaque redémarrage.
 */
class FileStore
{
    private static bool $tableReady = false;

    public static function usesDatabase(): bool
    {
        return strtolower((string) app_env('STORAGE_DRIVER', 'local')) === 'database';
    }

    /**
     * Enregistrer le fichier temporaire sous le nom donné.
     */
    public static function put(string $fileName, string $temporaryPath): void
    {
        $fileName = basename($fileName);

        if (self::usesDatabase()) {
            $stream = fopen($temporaryPath, 'rb');
            if ($stream === false) {
                throw new RuntimeException('Impossible de lire le document envoyé.');
            }

            try {
                $pdo = self::pdo();
                $statement = $pdo->prepare(
                    'INSERT INTO document_files (file_name, content) VALUES (:name, :content)'
                );
                $statement->bindValue(':name', $fileName);
                $statement->bindValue(':content', $stream, PDO::PARAM_LOB);
                $statement->execute();
            } finally {
                fclose($stream);
            }

            return;
        }

        $directory = STORAGE_PATH . '/documents';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Impossible de créer le dossier de stockage.');
        }

        if (!move_uploaded_file($temporaryPath, $directory . '/' . $fileName)) {
            throw new RuntimeException('Impossible d\'enregistrer le document.');
        }
    }

    /**
     * Lire le contenu d'un fichier (null s'il n'existe pas).
     */
    public static function read(string $fileName): ?string
    {
        $fileName = basename($fileName);

        if (self::usesDatabase()) {
            $statement = self::pdo()->prepare('SELECT content FROM document_files WHERE file_name = :name');
            $statement->bindValue(':name', $fileName);
            $statement->execute();
            $statement->bindColumn(1, $content, PDO::PARAM_LOB);

            if (!$statement->fetch(PDO::FETCH_BOUND)) {
                return null;
            }

            if (is_resource($content)) {
                $content = stream_get_contents($content);
            }

            return $content === false || $content === null ? null : (string) $content;
        }

        $path = STORAGE_PATH . '/documents/' . $fileName;

        if (!is_file($path)) {
            return null;
        }

        $content = file_get_contents($path);

        return $content === false ? null : $content;
    }

    /**
     * Supprimer un fichier (sans erreur s'il n'existe pas).
     */
    public static function delete(string $fileName): void
    {
        $fileName = basename($fileName);

        if (self::usesDatabase()) {
            self::pdo()
                ->prepare('DELETE FROM document_files WHERE file_name = :name')
                ->execute(['name' => $fileName]);

            return;
        }

        $path = STORAGE_PATH . '/documents/' . $fileName;
        if (is_file($path)) {
            unlink($path);
        }
    }

    private static function pdo(): PDO
    {
        $pdo = Database::connect();

        if (!self::$tableReady) {
            if (defined('DB_DRIVER') && DB_DRIVER === 'pgsql') {
                $pdo->exec(
                    'CREATE TABLE IF NOT EXISTS document_files (
                        file_name VARCHAR(255) PRIMARY KEY,
                        content BYTEA NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )'
                );
            } else {
                $pdo->exec(
                    'CREATE TABLE IF NOT EXISTS document_files (
                        file_name VARCHAR(255) NOT NULL PRIMARY KEY,
                        content LONGBLOB NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
                );
            }

            self::$tableReady = true;
        }

        return $pdo;
    }
}
