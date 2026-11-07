<?php

namespace App\Models;

use App\Core\Model;

class Setting extends Model
{
    /**
     * Récupérer tous les paramètres.
     */
    public static function getAll(): array
    {
        $sql = "
            SELECT *
            FROM settings
            ORDER BY setting_key ASC
        ";

        return self::query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer un paramètre par sa clé.
     */
    public static function get(string $key): ?string
    {
        $sql = "
            SELECT setting_value
            FROM settings
            WHERE setting_key = :setting_key
            LIMIT 1
        ";

        $result = self::query($sql, [
            'setting_key' => $key
        ]);

        $setting = $result->fetch(\PDO::FETCH_ASSOC);

        return $setting['setting_value'] ?? null;
    }

    /**
     * Enregistrer ou modifier un paramètre.
     */
    public static function set(string $key, string $value): bool
    {
        $existing = self::get($key);

        if ($existing !== null) {
            $sql = "
                UPDATE settings
                SET
                    setting_value = :setting_value,
                    updated_at = NOW()
                WHERE setting_key = :setting_key
            ";

            return self::execute($sql, [
                'setting_key' => $key,
                'setting_value' => $value
            ]);
        }

        $sql = "
            INSERT INTO settings (
                setting_key,
                setting_value,
                created_at,
                updated_at
            ) VALUES (
                :setting_key,
                :setting_value,
                NOW(),
                NOW()
            )
        ";

        return self::execute($sql, [
            'setting_key' => $key,
            'setting_value' => $value
        ]);
    }

    /**
     * Modifier plusieurs paramètres.
     */
    public static function updateMultiple(array $settings): bool
    {
        foreach ($settings as $key => $value) {
            if (!self::set((string) $key, (string) $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Supprimer un paramètre.
     */
    public static function delete(string $key): bool
    {
        $sql = "
            DELETE FROM settings
            WHERE setting_key = :setting_key
        ";

        return self::execute($sql, [
            'setting_key' => $key
        ]);
    }
}