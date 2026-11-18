<?php

namespace App\Services;

/**
 * Mots de passe temporaires faciles à dicter et à saisir : pas de caractères ambigus (0/O, 1/l/I),
 * regroupés par 4 (ex. « Kd7m-Qp4x-Rt9z »). 12 caractères tirés au hasard cryptographique.
 */
class TemporaryPassword
{
    private const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';

    public static function generate(): string
    {
        do {
            $raw = '';
            for ($i = 0; $i < 12; $i++) {
                $raw .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
            }
        } while (preg_match('/[A-Za-z]/', $raw) !== 1 || preg_match('/\d/', $raw) !== 1);

        return implode('-', str_split($raw, 4));
    }
}
