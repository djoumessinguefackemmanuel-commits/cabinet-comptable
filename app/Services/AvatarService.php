<?php

namespace App\Services;

use App\Core\Database;
use PDO;
use RuntimeException;

/**
 * Photos de profil, enregistrées en base (le disque est éphémère sur Vercel/Railway).
 *
 * L'image envoyée est contrôlée puis ré-encodée (recadrage carré 320x320, JPEG) : cela supprime
 * les métadonnées (GPS, EXIF) et tout contenu caché dans le fichier d'origine.
 */
class AvatarService
{
    public const MAX_UPLOAD_BYTES = 4 * 1024 * 1024;
    private const SIZE = 320;
    private const MAX_PIXELS = 40000000;

    private static bool $tableReady = false;

    /**
     * Enregistrer la photo d'un utilisateur à partir d'un fichier téléversé.
     *
     * @throws RuntimeException message affichable à l'utilisateur
     */
    public static function store(int $userId, array $file): void
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            throw new RuntimeException('La photo est trop volumineuse (4 Mo maximum).');
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Aucune photo reçue. Choisissez un fichier et réessayez.');
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if (!is_uploaded_file($tmp)) {
            throw new RuntimeException('Fichier invalide.');
        }
        if ((int) ($file['size'] ?? 0) > self::MAX_UPLOAD_BYTES) {
            throw new RuntimeException('La photo est trop volumineuse (4 Mo maximum).');
        }
        if (!extension_loaded('gd')) {
            throw new RuntimeException('Le traitement des images n\'est pas disponible sur ce serveur.');
        }

        $info = @getimagesize($tmp);
        $type = $info[2] ?? null;
        $loaders = [
            IMAGETYPE_JPEG => 'imagecreatefromjpeg',
            IMAGETYPE_PNG => 'imagecreatefrompng',
            IMAGETYPE_WEBP => 'imagecreatefromwebp',
        ];
        if ($info === false || !isset($loaders[$type]) || !function_exists($loaders[$type])) {
            throw new RuntimeException('Format non pris en charge. Utilisez une image JPEG, PNG ou WebP.');
        }
        if (($info[0] ?? 0) < 32 || ($info[1] ?? 0) < 32 || $info[0] * $info[1] > self::MAX_PIXELS) {
            throw new RuntimeException('Dimensions de l\'image invalides.');
        }

        $source = @$loaders[$type]($tmp);
        if ($source === false) {
            throw new RuntimeException('Image illisible ou corrompue.');
        }

        // Orientation EXIF (photos de téléphone).
        if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($tmp);
            $angle = match ((int) ($exif['Orientation'] ?? 1)) {
                3 => 180,
                6 => -90,
                8 => 90,
                default => 0,
            };
            if ($angle !== 0) {
                $rotated = imagerotate($source, $angle, 0);
                if ($rotated !== false) {
                    $source = $rotated;
                }
            }
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $side = min($width, $height);
        $canvas = imagecreatetruecolor(self::SIZE, self::SIZE);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopyresampled(
            $canvas, $source, 0, 0,
            intdiv($width - $side, 2), intdiv($height - $side, 2),
            self::SIZE, self::SIZE, $side, $side
        );

        ob_start();
        imagejpeg($canvas, null, 85);
        $jpeg = (string) ob_get_clean();
        if ($jpeg === '') {
            throw new RuntimeException('Impossible de traiter l\'image.');
        }

        $pdo = self::pdo();
        $pdo->prepare('DELETE FROM user_avatars WHERE user_id = ?')->execute([$userId]);
        $statement = $pdo->prepare(
            'INSERT INTO user_avatars (user_id, mime, content, updated_at) VALUES (?, ?, ?, ?)'
        );
        $statement->bindValue(1, $userId, PDO::PARAM_INT);
        $statement->bindValue(2, 'image/jpeg');
        $statement->bindValue(3, $jpeg, PDO::PARAM_LOB);
        $statement->bindValue(4, time(), PDO::PARAM_INT);
        $statement->execute();
    }

    /** @return array{mime:string,content:string}|null */
    public static function read(int $userId): ?array
    {
        $statement = self::pdo()->prepare('SELECT mime, content FROM user_avatars WHERE user_id = ?');
        $statement->execute([$userId]);
        $statement->bindColumn(1, $mime);
        $statement->bindColumn(2, $content, PDO::PARAM_LOB);

        if (!$statement->fetch(PDO::FETCH_BOUND)) {
            return null;
        }
        if (is_resource($content)) {
            $content = stream_get_contents($content);
        }

        return $content === false || $content === null ? null : ['mime' => (string) $mime, 'content' => (string) $content];
    }

    /** Numéro de version de la photo (pour l'URL), ou null s'il n'y en a pas. */
    public static function version(int $userId): ?int
    {
        try {
            $statement = self::pdo()->prepare('SELECT updated_at FROM user_avatars WHERE user_id = ?');
            $statement->execute([$userId]);
            $value = $statement->fetchColumn();

            return $value === false ? null : (int) $value;
        } catch (\Throwable $exception) {
            error_log('AvatarService::version : ' . $exception->getMessage());
            return null;
        }
    }

    public static function delete(int $userId): void
    {
        try {
            self::pdo()->prepare('DELETE FROM user_avatars WHERE user_id = ?')->execute([$userId]);
        } catch (\Throwable $exception) {
            error_log('AvatarService::delete : ' . $exception->getMessage());
        }
    }

    private static function pdo(): PDO
    {
        $pdo = Database::connect();

        if (!self::$tableReady) {
            if (defined('DB_DRIVER') && DB_DRIVER === 'pgsql') {
                $pdo->exec(
                    'CREATE TABLE IF NOT EXISTS user_avatars (
                        user_id INTEGER PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
                        mime VARCHAR(50) NOT NULL,
                        content BYTEA NOT NULL,
                        updated_at BIGINT NOT NULL
                    )'
                );
            } else {
                $pdo->exec(
                    'CREATE TABLE IF NOT EXISTS user_avatars (
                        user_id INT UNSIGNED NOT NULL PRIMARY KEY,
                        mime VARCHAR(50) NOT NULL,
                        content MEDIUMBLOB NOT NULL,
                        updated_at BIGINT NOT NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
                );
            }
            self::$tableReady = true;
        }

        return $pdo;
    }
}
