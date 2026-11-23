<?php

namespace App\Services;

/**
 * Lecture d'un relevé bancaire au format CSV (export de la banque ou d'Excel).
 *
 * Colonnes reconnues (avec ou sans ligne d'en-tête) : date, libellé, débit, crédit ou montant, référence.
 * Convention du relevé : « débit » = sortie d'argent (montant négatif), « crédit » = entrée (montant positif).
 * Sans en-tête : date ; libellé ; montant   ou   date ; libellé ; débit ; crédit.
 */
class BankStatementParser
{
    public const MAX_BYTES = 2097152;
    public const MAX_ROWS = 5000;

    /** @return array{lines: array<int, array{date:string,label:string,amount:float,reference:?string}>, errors: string[]} */
    public static function parse(string $content): array
    {
        $content = self::toUtf8($content);
        $rows = preg_split('/\r\n|\r|\n/', trim($content)) ?: [];
        $rows = array_values(array_filter($rows, static fn ($r) => trim($r) !== ''));

        if ($rows === []) {
            return ['lines' => [], 'errors' => ['Le fichier est vide.']];
        }
        if (count($rows) > self::MAX_ROWS + 1) {
            return ['lines' => [], 'errors' => ['Le fichier dépasse ' . self::MAX_ROWS . ' lignes : découpez-le par période.']];
        }

        $delimiter = self::detectDelimiter($rows[0]);
        $table = array_map(static fn ($r) => array_map('trim', str_getcsv($r, $delimiter, '"', '')), $rows);

        $map = self::mapHeader($table[0]);
        $start = 0;
        if ($map !== null) {
            $start = 1;
        } else {
            $count = count($table[0]);
            $map = $count >= 4
                ? ['date' => 0, 'label' => 1, 'debit' => 2, 'credit' => 3]
                : ['date' => 0, 'label' => 1, 'amount' => 2];
        }

        $lines = [];
        $errors = [];
        for ($i = $start; $i < count($table); $i++) {
            $row = $table[$i];
            $n = $i + 1;

            $date = self::parseDate($row[$map['date']] ?? '');
            if ($date === null) {
                $errors[] = "Ligne {$n} ignorée : date illisible (« " . ($row[$map['date']] ?? '') . ' »).';
                continue;
            }

            if (isset($map['amount'])) {
                $amount = self::parseAmount($row[$map['amount']] ?? '');
            } else {
                $debit = self::parseAmount($row[$map['debit']] ?? '');
                $credit = self::parseAmount($row[$map['credit']] ?? '');
                $amount = $debit === null && $credit === null ? null : (($credit ?? 0.0) - abs($debit ?? 0.0));
            }

            if ($amount === null || abs($amount) < 0.005) {
                $errors[] = "Ligne {$n} ignorée : montant absent ou nul.";
                continue;
            }

            $label = trim((string) ($row[$map['label'] ?? -1] ?? ''));
            $lines[] = [
                'date' => $date,
                'label' => mb_substr($label !== '' ? $label : 'Opération bancaire', 0, 255),
                'amount' => round($amount, 2),
                'reference' => isset($map['reference']) && ($row[$map['reference']] ?? '') !== ''
                    ? mb_substr((string) $row[$map['reference']], 0, 100) : null,
            ];
        }

        if ($lines === [] && $errors === []) {
            $errors[] = 'Aucune opération trouvée dans ce fichier.';
        }

        return ['lines' => $lines, 'errors' => $errors];
    }

    private static function toUtf8(string $content): string
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;

        return mb_check_encoding($content, 'UTF-8')
            ? $content
            : mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
    }

    private static function detectDelimiter(string $line): string
    {
        $best = ';';
        $max = -1;
        foreach ([';', "\t", ',', '|'] as $d) {
            $c = substr_count($line, $d);
            if ($c > $max) {
                $max = $c;
                $best = $d;
            }
        }

        return $best;
    }

    private static function norm(string $s): string
    {
        $s = mb_strtolower(trim($s));

        return strtr($s, ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'à' => 'a', 'ô' => 'o', 'î' => 'i', 'ç' => 'c', 'û' => 'u']);
    }

    /** @return array<string,int>|null null si la première ligne n'est pas un en-tête */
    private static function mapHeader(array $first): ?array
    {
        foreach ($first as $cell) {
            if (self::parseDate($cell) !== null) {
                return null;
            }
        }

        $map = [];
        foreach ($first as $i => $cell) {
            $c = self::norm($cell);
            if ($c === '') {
                continue;
            }
            if (!isset($map['date']) && str_contains($c, 'date')) {
                $map['date'] = $i;
            } elseif (!isset($map['debit']) && (str_contains($c, 'debit') || str_contains($c, 'retrait') || str_contains($c, 'sortie') || str_contains($c, 'paiement'))) {
                $map['debit'] = $i;
            } elseif (!isset($map['credit']) && (str_contains($c, 'credit') || str_contains($c, 'depot') || str_contains($c, 'entree') || str_contains($c, 'versement'))) {
                $map['credit'] = $i;
            } elseif (!isset($map['amount']) && (str_contains($c, 'montant') || str_contains($c, 'amount') || str_contains($c, 'somme'))) {
                $map['amount'] = $i;
            } elseif (!isset($map['reference']) && (str_contains($c, 'ref') || str_contains($c, 'piece') || str_contains($c, 'numero'))) {
                $map['reference'] = $i;
            } elseif (!isset($map['label']) && (str_contains($c, 'libelle') || str_contains($c, 'description') || str_contains($c, 'label') || str_contains($c, 'operation') || str_contains($c, 'detail') || str_contains($c, 'motif'))) {
                $map['label'] = $i;
            }
        }

        if (!isset($map['date']) || (!isset($map['amount']) && !isset($map['debit']) && !isset($map['credit']))) {
            return null;
        }
        if (!isset($map['amount'])) {
            $map['debit'] ??= -1;
            $map['credit'] ??= -1;
        }
        if (!isset($map['label'])) {
            foreach ($first as $i => $_) {
                if (!in_array($i, $map, true)) {
                    $map['label'] = $i;
                    break;
                }
            }
        }

        return $map;
    }

    public static function parseDate(string $value): ?string
    {
        $value = trim($value);
        if (preg_match('#^(\d{1,2})[/.\-](\d{1,2})[/.\-](\d{2}|\d{4})(?:\s.*)?$#', $value, $m)) {
            $y = (int) $m[3] < 100 ? 2000 + (int) $m[3] : (int) $m[3];

            return checkdate((int) $m[2], (int) $m[1], $y) ? sprintf('%04d-%02d-%02d', $y, $m[2], $m[1]) : null;
        }
        if (preg_match('#^(\d{4})[/.\-](\d{1,2})[/.\-](\d{1,2})(?:[T\s].*)?$#', $value, $m)) {
            return checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]) : null;
        }

        return null;
    }

    public static function parseAmount(string $value): ?float
    {
        $v = trim($value);
        if ($v === '') {
            return null;
        }

        $negative = str_starts_with($v, '-') || (str_starts_with($v, '(') && str_ends_with($v, ')')) || preg_match('/-\s*$/', $v) === 1;
        $v = preg_replace('/[^\d.,]/u', '', $v) ?? '';
        if ($v === '' || !preg_match('/\d/', $v)) {
            return null;
        }

        $lastComma = strrpos($v, ',');
        $lastDot = strrpos($v, '.');
        if ($lastComma !== false && $lastDot !== false) {
            // Le dernier séparateur est le séparateur décimal.
            $dec = $lastComma > $lastDot ? ',' : '.';
            $thousand = $dec === ',' ? '.' : ',';
            $v = str_replace($thousand, '', $v);
            $v = str_replace($dec, '.', $v);
        } elseif ($lastComma !== false || $lastDot !== false) {
            $sep = $lastComma !== false ? ',' : '.';
            $pos = $lastComma !== false ? $lastComma : $lastDot;
            $digitsAfter = strlen($v) - $pos - 1;
            if (substr_count($v, $sep) > 1 || $digitsAfter === 3) {
                $v = str_replace($sep, '', $v);   // séparateur de milliers
            } else {
                $v = str_replace($sep, '.', $v);  // séparateur décimal
            }
        }

        $n = (float) $v;

        return $negative ? -$n : $n;
    }
}
