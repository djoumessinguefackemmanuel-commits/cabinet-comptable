<?php

namespace App\Services;

/**
 * Numéros de téléphone avec indicatif du pays (format stocké : "+237 670000000").
 */
class PhoneNumber
{
    public const DEFAULT_CODE = '+237';

    /** Indicatif => libellé (Cameroun en premier, puis zones CEMAC/UEMOA et principaux pays). */
    public const COUNTRIES = [
        '+237' => 'Cameroun', '+241' => 'Gabon', '+242' => 'Congo', '+243' => 'RD Congo', '+235' => 'Tchad',
        '+236' => 'Centrafrique', '+240' => 'Guinée équatoriale', '+225' => 'Côte d\'Ivoire', '+221' => 'Sénégal',
        '+223' => 'Mali', '+226' => 'Burkina Faso', '+227' => 'Niger', '+228' => 'Togo', '+229' => 'Bénin',
        '+224' => 'Guinée', '+212' => 'Maroc', '+213' => 'Algérie', '+216' => 'Tunisie', '+234' => 'Nigeria',
        '+233' => 'Ghana', '+27' => 'Afrique du Sud', '+33' => 'France', '+32' => 'Belgique', '+41' => 'Suisse',
        '+44' => 'Royaume-Uni', '+49' => 'Allemagne', '+34' => 'Espagne', '+39' => 'Italie', '+1' => 'États-Unis / Canada',
        '+971' => 'Émirats arabes unis', '+86' => 'Chine',
    ];

    /** Assemble indicatif + numéro local en "+237 670000000". */
    public static function combine(?string $code, ?string $local): string
    {
        $code = trim((string) $code);
        $code = isset(self::COUNTRIES[$code]) ? $code : self::DEFAULT_CODE;
        $digits = preg_replace('/\D+/', '', (string) $local) ?? '';

        if ($digits === '') {
            return '';
        }

        // Numéro saisi avec l'indicatif (+237…, 00237…) : on ne le répète pas.
        $codeDigits = ltrim($code, '+');
        if (str_starts_with($digits, '00' . $codeDigits)) {
            $digits = substr($digits, 2 + strlen($codeDigits));
        } elseif (str_starts_with((string) $local, '+') && str_starts_with($digits, $codeDigits)) {
            $digits = substr($digits, strlen($codeDigits));
        }

        // Zéro de tête national supprimé (0612… -> 612…).
        $digits = ltrim($digits, '0') === '' ? $digits : (str_starts_with($digits, '0') ? substr($digits, 1) : $digits);

        return $code . ' ' . $digits;
    }

    /** @return array{0:string,1:string} [indicatif, numéro local] d'un numéro enregistré. */
    public static function split(?string $full): array
    {
        $full = trim((string) $full);
        $compact = preg_replace('/[\s.\-()]+/', '', $full) ?? '';

        if ($compact !== '' && $compact[0] === '+') {
            $codes = array_keys(self::COUNTRIES);
            usort($codes, static fn ($a, $b) => strlen($b) <=> strlen($a));
            foreach ($codes as $code) {
                if (str_starts_with($compact, $code)) {
                    return [$code, substr($compact, strlen($code))];
                }
            }
        }

        return [self::DEFAULT_CODE, ltrim($compact, '+')];
    }
}
