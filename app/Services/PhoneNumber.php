<?php

namespace App\Services;

/**
 * Numéros de téléphone avec indicatif du pays (format stocké : "+237 670000000").
 */
class PhoneNumber
{
    public const DEFAULT_CODE = '+237';

    /** Pays d'Afrique (54), Cameroun en premier puis ordre alphabétique. */
    public const AFRICA = [
        '+237' => 'Cameroun',
        '+27' => 'Afrique du Sud',
        '+213' => 'Algérie',
        '+244' => 'Angola',
        '+267' => 'Botswana',
        '+226' => 'Burkina Faso',
        '+257' => 'Burundi',
        '+229' => 'Bénin',
        '+238' => 'Cabo Verde',
        '+236' => 'Centrafrique',
        '+269' => 'Comores',
        '+242' => 'Congo',
        '+225' => 'Côte d\'Ivoire',
        '+253' => 'Djibouti',
        '+20' => 'Égypte',
        '+291' => 'Érythrée',
        '+268' => 'Eswatini',
        '+251' => 'Éthiopie',
        '+241' => 'Gabon',
        '+220' => 'Gambie',
        '+233' => 'Ghana',
        '+224' => 'Guinée',
        '+240' => 'Guinée équatoriale',
        '+245' => 'Guinée-Bissau',
        '+254' => 'Kenya',
        '+266' => 'Lesotho',
        '+218' => 'Libye',
        '+231' => 'Libéria',
        '+261' => 'Madagascar',
        '+265' => 'Malawi',
        '+223' => 'Mali',
        '+212' => 'Maroc',
        '+230' => 'Maurice',
        '+222' => 'Mauritanie',
        '+258' => 'Mozambique',
        '+264' => 'Namibie',
        '+227' => 'Niger',
        '+234' => 'Nigeria',
        '+256' => 'Ouganda',
        '+243' => 'RD Congo',
        '+250' => 'Rwanda',
        '+239' => 'Sao Tomé-et-Principe',
        '+248' => 'Seychelles',
        '+232' => 'Sierra Leone',
        '+252' => 'Somalie',
        '+249' => 'Soudan',
        '+211' => 'Soudan du Sud',
        '+221' => 'Sénégal',
        '+255' => 'Tanzanie',
        '+235' => 'Tchad',
        '+228' => 'Togo',
        '+216' => 'Tunisie',
        '+260' => 'Zambie',
        '+263' => 'Zimbabwe',
    ];

    /** Autres pays fréquents. */
    public const OTHERS = [
        '+33' => 'France',
        '+32' => 'Belgique',
        '+41' => 'Suisse',
        '+44' => 'Royaume-Uni',
        '+49' => 'Allemagne',
        '+34' => 'Espagne',
        '+39' => 'Italie',
        '+1' => 'États-Unis / Canada',
        '+971' => 'Émirats arabes unis',
        '+86' => 'Chine',
    ];

    public const COUNTRIES = [...self::AFRICA, ...self::OTHERS];

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
