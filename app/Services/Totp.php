<?php

namespace App\Services;

/**
 * Mots de passe à usage unique basés sur le temps (TOTP, RFC 6238).
 * Compatible avec Google Authenticator, Microsoft Authenticator, Authy, 1Password…
 */
class Totp
{
    public const PERIOD = 30;
    public const DIGITS = 6;

    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /** Générer un secret aléatoire de 160 bits, encodé en base32. */
    public static function generateSecret(): string
    {
        return self::base32Encode(random_bytes(20));
    }

    /** Code à 6 chiffres pour le secret et le pas de temps donnés. */
    public static function code(string $secret, int $step): string
    {
        $key = self::base32Decode($secret);
        $counter = pack('N2', ($step >> 32) & 0xFFFFFFFF, $step & 0xFFFFFFFF);
        $hash = hash_hmac('sha1', $counter, $key, true);

        $offset = ord($hash[19]) & 0x0F;
        $binary = ((ord($hash[$offset]) & 0x7F) << 24)
            | (ord($hash[$offset + 1]) << 16)
            | (ord($hash[$offset + 2]) << 8)
            | ord($hash[$offset + 3]);

        return str_pad((string) ($binary % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Vérifier un code saisi (tolérance d'un pas avant/après pour les horloges décalées).
     *
     * @param int $afterStep  pas déjà utilisé : un même code ne peut servir qu'une fois
     * @return int|null  pas de temps validé, ou null si le code est invalide
     */
    public static function verify(string $secret, string $input, int $afterStep = 0, ?int $now = null): ?int
    {
        $input = preg_replace('/\s+/', '', $input) ?? '';
        if (!preg_match('/^\d{6}$/', $input)) {
            return null;
        }

        $current = intdiv($now ?? time(), self::PERIOD);

        for ($offset = -1; $offset <= 1; $offset++) {
            $step = $current + $offset;
            if ($step <= $afterStep) {
                continue;
            }
            if (hash_equals(self::code($secret, $step), $input)) {
                return $step;
            }
        }

        return null;
    }

    /** Adresse otpauth:// à convertir en QR code pour l'application d'authentification. */
    public static function uri(string $secret, string $account, string $issuer): string
    {
        // SHA1, 6 chiffres, 30 s sont les valeurs par défaut de toutes les applications :
        // les omettre raccourcit le QR code (moins de modules, donc plus facile à scanner).
        return 'otpauth://totp/' . rawurlencode($issuer . ':' . $account)
            . '?secret=' . $secret
            . '&issuer=' . rawurlencode($issuer);
    }

    public static function base32Encode(string $data): string
    {
        $bits = '';
        foreach (str_split($data) as $char) {
            $bits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $encoded = '';
        foreach (str_split($bits, 5) as $chunk) {
            $encoded .= self::ALPHABET[bindec(str_pad($chunk, 5, '0'))];
        }

        return $encoded;
    }

    public static function base32Decode(string $encoded): string
    {
        $encoded = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $encoded) ?? '');
        $bits = '';
        foreach (str_split($encoded) as $char) {
            $bits .= str_pad(decbin((int) strpos(self::ALPHABET, $char)), 5, '0', STR_PAD_LEFT);
        }

        $decoded = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $decoded .= chr(bindec($byte));
            }
        }

        return $decoded;
    }
}
