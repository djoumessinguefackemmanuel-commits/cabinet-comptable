<?php

namespace App\Services;

/**
 * Amortissement linéaire, prorata temporis en base 30/360 (usage SYSCOHADA).
 * Fonction cumulative : la dotation d'une période quelconque (exercice civil ou non) est la différence de cumuls.
 */
class Depreciation
{
    /** Jours 30/360 entre deux dates (Y-m-d), sans valeur négative. */
    public static function days360(string $from, string $to): int
    {
        [$y1, $m1, $d1] = array_map('intval', explode('-', $from));
        [$y2, $m2, $d2] = array_map('intval', explode('-', $to));
        $d1 = min($d1, 30);
        $d2 = min($d2, 30);

        return max(0, ($y2 - $y1) * 360 + ($m2 - $m1) * 30 + ($d2 - $d1));
    }

    /** Amortissement cumulé à la fin du jour $date inclus. */
    public static function cumulative(array $asset, string $date): float
    {
        $base = (float) $asset['cost'] - (float) ($asset['residual_value'] ?? 0);
        $years = max(1, (int) $asset['useful_life_years']);
        $end = $date;
        if (!empty($asset['disposal_date']) && $asset['disposal_date'] < $end) {
            $end = $asset['disposal_date'];
        }

        $next = date('Y-m-d', strtotime($end . ' +1 day'));
        $ratio = min(1.0, self::days360($asset['acquisition_date'], $next) / ($years * 360));

        return round($base * $ratio, 2);
    }

    /** Dotation de la période [start, end] (bornes incluses). */
    public static function forPeriod(array $asset, string $start, string $end): float
    {
        $before = date('Y-m-d', strtotime($start . ' -1 day'));

        return round(self::cumulative($asset, $end) - self::cumulative($asset, $before), 2);
    }

    /** Plan par année civile. @return array<int,array{year:int,dotation:float,cumul:float,vnc:float}> */
    public static function schedule(array $asset): array
    {
        $rows = [];
        $first = (int) substr($asset['acquisition_date'], 0, 4);
        $years = max(1, (int) $asset['useful_life_years']);
        $cost = (float) $asset['cost'];

        for ($y = $first; $y <= $first + $years; $y++) {
            $d = self::forPeriod($asset, "$y-01-01", "$y-12-31");
            $cum = self::cumulative($asset, "$y-12-31");
            if ($d > 0) {
                $rows[] = ['year' => $y, 'dotation' => $d, 'cumul' => $cum, 'vnc' => round($cost - $cum, 2)];
            }
        }

        return $rows;
    }
}
