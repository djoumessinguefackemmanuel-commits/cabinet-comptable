<?php

namespace App\Services;

/**
 * Générateur PDF minimal (tableaux), sans dépendance externe.
 *
 * Police Helvetica intégrée à tout lecteur PDF, encodage WinAnsi (accents français),
 * retour à la ligne automatique dans les cellules, en-têtes répétés à chaque page,
 * pagination « Page x / y ». Passage automatique en paysage si le tableau est large.
 */
class PdfTable
{
    /** Largeurs Helvetica (1/1000 em) pour les codes 32 à 126. */
    private const WIDTHS = [
        278, 278, 355, 556, 556, 889, 667, 191, 333, 333, 389, 584, 278, 333, 278, 278,
        556, 556, 556, 556, 556, 556, 556, 556, 556, 556, 278, 278, 584, 584, 584, 556,
        1015, 667, 667, 722, 722, 667, 611, 778, 722, 278, 500, 667, 556, 833, 722, 778,
        667, 778, 722, 667, 611, 722, 667, 944, 667, 667, 611, 278, 278, 278, 469, 556,
        333, 556, 556, 500, 556, 556, 278, 556, 556, 222, 222, 500, 222, 833, 556, 556,
        556, 556, 333, 500, 278, 556, 500, 722, 500, 500, 500, 334, 260, 334, 584,
    ];

    /** Helvetica-Bold : approximation (≈ +6 %) suffisante pour la mise en page. */
    private const BOLD_FACTOR = 1.06;

    private string $title;
    private string $subtitle;

    private float $pageWidth = 595.28;
    private float $pageHeight = 841.89;
    private float $margin = 34.0;
    private float $fontSize = 8.5;
    private float $leading = 11.0;
    private float $padding = 4.0;

    /** @var string[] */
    private array $pages = [];
    private string $current = '';
    private float $y = 0.0;

    public function __construct(string $title, array $meta = [])
    {
        $this->title = $title;
        $this->subtitle = (string) ($meta['subtitle'] ?? '');
    }

    public function render(array $tables): string
    {
        $maxColumns = 0;
        foreach ($tables as $table) {
            $maxColumns = max($maxColumns, count($table['headers']));
        }

        if ($maxColumns > 5) {
            [$this->pageWidth, $this->pageHeight] = [$this->pageHeight, $this->pageWidth];
        }

        $this->newPage();
        $this->drawTitle();

        foreach ($tables as $table) {
            $this->drawTable($table);
        }

        $this->pages[] = $this->current;

        return $this->build();
    }

    /* ---------------------------------------------------------------
     * Mise en page
     * ------------------------------------------------------------- */

    private function newPage(): void
    {
        if ($this->current !== '') {
            $this->pages[] = $this->current;
        }
        $this->current = '';
        $this->y = $this->margin;
    }

    private function bottomLimit(): float
    {
        return $this->pageHeight - $this->margin - 14;
    }

    private function drawTitle(): void
    {
        $this->text($this->margin, $this->y + 14, $this->title, 15, true, [0.1, 0.2, 0.35]);
        $this->y += 22;

        $when = 'Généré le ' . date('d/m/Y à H:i');
        $line = $this->subtitle !== '' ? $this->subtitle . '   |   ' . $when : $when;
        $this->text($this->margin, $this->y + 8, $line, 8.5, false, [0.45, 0.5, 0.56]);
        $this->y += 20;
    }

    private function drawTable(array $table): void
    {
        $columns = count($table['headers']);
        if ($columns === 0) {
            return;
        }

        $usable = $this->pageWidth - 2 * $this->margin;
        $widths = $this->columnWidths($table, $usable);

        $numeric = array_fill(0, $columns, true);
        foreach ($table['rows'] as $row) {
            foreach ($row as $i => $value) {
                if ($value !== '' && TableExporter::toNumber($value) === null) {
                    $numeric[$i] = false;
                }
            }
        }

        if ($table['title'] !== '') {
            if ($this->y + 40 > $this->bottomLimit()) {
                $this->newPage();
            }
            $this->text($this->margin, $this->y + 10, $table['title'], 10.5, true, [0.1, 0.2, 0.35]);
            $this->y += 18;
        }

        $this->drawRow($table['headers'], $widths, $numeric, true, false);

        foreach ($table['rows'] as $index => $row) {
            $height = $this->rowHeight($row, $widths);

            if ($this->y + $height > $this->bottomLimit()) {
                $this->newPage();
                $this->drawRow($table['headers'], $widths, $numeric, true, false);
            }

            $this->drawRow($row, $widths, $numeric, false, $index % 2 === 1);
        }

        $this->y += 14;
    }

    private function columnWidths(array $table, float $usable): array
    {
        $columns = count($table['headers']);
        $natural = [];

        for ($i = 0; $i < $columns; $i++) {
            $longest = $this->width($table['headers'][$i], true);
            $wordMax = 0.0;
            foreach ($table['rows'] as $row) {
                $longest = max($longest, $this->width($row[$i] ?? '', false));
                foreach (preg_split('/\s+/u', $row[$i] ?? '') ?: [] as $word) {
                    $wordMax = max($wordMax, $this->width($word, false));
                }
            }
            $natural[$i] = [
                'want' => min($longest, $usable * 0.45) + 2 * $this->padding,
                'min' => min(max($wordMax, 30.0), $usable * 0.3) + 2 * $this->padding,
            ];
        }

        $total = array_sum(array_column($natural, 'want'));

        if ($total <= $usable) {
            // Répartir l'espace restant proportionnellement.
            $extra = $usable - $total;
            return array_map(static fn(array $c): float => $c['want'] + $extra * $c['want'] / $total, $natural);
        }

        // Trop large : réduire proportionnellement, sans descendre sous le minimum.
        $widths = array_column($natural, 'want');
        $min = array_column($natural, 'min');
        for ($pass = 0; $pass < 6; $pass++) {
            $sum = array_sum($widths);
            if ($sum <= $usable + 0.01) {
                break;
            }
            $shrinkable = 0.0;
            foreach ($widths as $i => $w) {
                $shrinkable += max(0.0, $w - $min[$i]);
            }
            if ($shrinkable <= 0.0) {
                break;
            }
            $ratio = min(1.0, ($sum - $usable) / $shrinkable);
            foreach ($widths as $i => $w) {
                $widths[$i] = $w - max(0.0, $w - $min[$i]) * $ratio;
            }
        }

        $sum = array_sum($widths);

        return $sum > $usable ? array_map(static fn(float $w): float => $w * $usable / $sum, $widths) : $widths;
    }

    private function rowHeight(array $cells, array $widths): float
    {
        $lines = 1;
        foreach ($widths as $i => $width) {
            $lines = max($lines, count($this->wrap($cells[$i] ?? '', $width - 2 * $this->padding, false)));
        }

        return $lines * $this->leading + 2 * $this->padding - 2;
    }

    private function drawRow(array $cells, array $widths, array $numeric, bool $header, bool $zebra): void
    {
        $height = $this->rowHeight($cells, $widths);
        $x = $this->margin;
        $total = array_sum($widths);

        if ($header) {
            $this->rect($x, $this->y, $total, $height, [0.91, 0.93, 0.96]);
        } elseif ($zebra) {
            $this->rect($x, $this->y, $total, $height, [0.972, 0.978, 0.985]);
        }

        foreach ($widths as $i => $width) {
            $lines = $this->wrap($cells[$i] ?? '', $width - 2 * $this->padding, $header);
            $lineY = $this->y + $this->padding + $this->fontSize - 1;

            foreach ($lines as $line) {
                $textX = $x + $this->padding;
                if ($numeric[$i] && !$header) {
                    $textX = $x + $width - $this->padding - $this->width($line, false);
                }
                $this->text($textX, $lineY, $line, $this->fontSize, $header, $header ? [0.1, 0.2, 0.35] : [0.12, 0.16, 0.22]);
                $lineY += $this->leading;
            }
            $x += $width;
        }

        // Filet inférieur
        $this->line($this->margin, $this->y + $height, $this->margin + $total, $this->y + $height, $header ? 0.8 : 0.4, [0.82, 0.85, 0.89]);
        $this->y += $height;
    }

    /* ---------------------------------------------------------------
     * Texte : conversion, mesure, retour à la ligne
     * ------------------------------------------------------------- */

    private function encode(string $text): string
    {
        $text = str_replace(["\xC2\xA0", "\xE2\x80\xAF", "\xE2\x86\x92"], [' ', ' ', '->'], $text);
        $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);

        return $converted === false ? '' : $converted;
    }

    private function width(string $text, bool $bold): float
    {
        $sum = 0;
        foreach (str_split($this->encode($text)) as $char) {
            $code = ord($char);
            $sum += ($code >= 32 && $code <= 126) ? self::WIDTHS[$code - 32] : $this->accentWidth($code);
        }

        return $sum / 1000 * $this->fontSize * ($bold ? self::BOLD_FACTOR : 1.0) * 1.01;
    }

    private function accentWidth(int $code): int
    {
        // Lettres accentuées : même largeur que la lettre de base.
        static $base = [
            0xE0 => 'a', 0xE1 => 'a', 0xE2 => 'a', 0xE3 => 'a', 0xE4 => 'a', 0xE5 => 'a', 0xE7 => 'c',
            0xE8 => 'e', 0xE9 => 'e', 0xEA => 'e', 0xEB => 'e', 0xEC => 'i', 0xED => 'i', 0xEE => 'i', 0xEF => 'i',
            0xF1 => 'n', 0xF2 => 'o', 0xF3 => 'o', 0xF4 => 'o', 0xF5 => 'o', 0xF6 => 'o',
            0xF9 => 'u', 0xFA => 'u', 0xFB => 'u', 0xFC => 'u', 0xFF => 'y',
            0xC0 => 'A', 0xC1 => 'A', 0xC2 => 'A', 0xC3 => 'A', 0xC4 => 'A', 0xC5 => 'A', 0xC7 => 'C',
            0xC8 => 'E', 0xC9 => 'E', 0xCA => 'E', 0xCB => 'E', 0xCC => 'I', 0xCD => 'I', 0xCE => 'I', 0xCF => 'I',
            0xD1 => 'N', 0xD2 => 'O', 0xD3 => 'O', 0xD4 => 'O', 0xD5 => 'O', 0xD6 => 'O',
            0xD9 => 'U', 0xDA => 'U', 0xDB => 'U', 0xDC => 'U',
        ];

        if (isset($base[$code])) {
            return self::WIDTHS[ord($base[$code]) - 32];
        }

        return match ($code) {
            0x80 => 556,   // €
            0x92, 0x91 => 222,
            0x93, 0x94 => 333,
            0x96 => 556, 0x97 => 1000,
            0xA0 => 278,
            0xAB, 0xBB => 556,
            0xB0 => 400,
            0xE6 => 889, 0x9C => 944, 0x8C => 1000,
            default => 556,
        };
    }

    /**
     * @return string[]
     */
    private function wrap(string $text, float $maxWidth, bool $bold): array
    {
        if ($text === '') {
            return [''];
        }

        $lines = [];
        $line = '';

        foreach (preg_split('/\s+/u', $text) ?: [] as $word) {
            // Mot plus large que la colonne : coupure par caractères.
            while ($this->width($word, $bold) > $maxWidth && mb_strlen($word) > 1) {
                $cut = mb_strlen($word) - 1;
                while ($cut > 1 && $this->width(mb_substr($word, 0, $cut), $bold) > $maxWidth) {
                    $cut--;
                }
                if ($line !== '') {
                    $lines[] = $line;
                    $line = '';
                }
                $lines[] = mb_substr($word, 0, $cut);
                $word = mb_substr($word, $cut);
            }

            $candidate = $line === '' ? $word : $line . ' ' . $word;
            if ($line !== '' && $this->width($candidate, $bold) > $maxWidth) {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $candidate;
            }
        }

        $lines[] = $line;

        return $lines;
    }

    /* ---------------------------------------------------------------
     * Primitives de dessin (origine en haut à gauche → converties)
     * ------------------------------------------------------------- */

    private function text(float $x, float $baselineY, string $text, float $size, bool $bold, array $color): void
    {
        $y = $this->pageHeight - $baselineY;
        $escaped = str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '', ''], $this->encode($text));

        $this->current .= sprintf(
            "BT %s rg /%s %.2F Tf %.2F %.2F Td (%s) Tj ET\n",
            $this->rgb($color), $bold ? 'F2' : 'F1', $size, $x, $y, $escaped
        );
    }

    private function rect(float $x, float $top, float $w, float $h, array $color): void
    {
        $this->current .= sprintf("%s rg %.2F %.2F %.2F %.2F re f\n", $this->rgb($color), $x, $this->pageHeight - $top - $h, $w, $h);
    }

    private function line(float $x1, float $y1, float $x2, float $y2, float $width, array $color): void
    {
        $this->current .= sprintf(
            "%s RG %.2F w %.2F %.2F m %.2F %.2F l S\n",
            $this->rgb($color), $width, $x1, $this->pageHeight - $y1, $x2, $this->pageHeight - $y2
        );
    }

    private function rgb(array $c): string
    {
        return sprintf('%.3F %.3F %.3F', $c[0], $c[1], $c[2]);
    }

    /* ---------------------------------------------------------------
     * Assemblage du fichier PDF
     * ------------------------------------------------------------- */

    private function build(): string
    {
        $count = count($this->pages);
        $objects = [];

        // 1 = catalogue, 2 = pages, 3 et 4 = polices, ensuite (contenu, page) par page.
        $kids = [];
        for ($i = 0; $i < $count; $i++) {
            $kids[] = (6 + 2 * $i) . ' 0 R';
        }

        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . $count . ' >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
        $objects[5] = '<< /Title (' . str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $this->encode($this->title)) . ') /Producer (Cabinet Comptable) >>';

        foreach ($this->pages as $i => $content) {
            $footer = $this->footer($i + 1, $count);
            $stream = gzcompress($content . $footer, 6);

            $pageId = 6 + 2 * $i;
            $streamId = $pageId + 1;

            $objects[$pageId] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2F %.2F] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents %d 0 R >>',
                $this->pageWidth, $this->pageHeight, $streamId
            );
            $objects[$streamId] = '<< /Length ' . strlen($stream) . ' /Filter /FlateDecode >>' . "\nstream\n" . $stream . "\nendstream";
        }

        ksort($objects);

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [];
        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $body . "\nendobj\n";
        }

        $size = max(array_keys($objects)) + 1;
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . $size . "\n0000000000 65535 f \n";
        for ($i = 1; $i < $size; $i++) {
            $pdf .= isset($offsets[$i]) ? sprintf("%010d 00000 n \n", $offsets[$i]) : "0000000000 65535 f \n";
        }
        $pdf .= "trailer\n<< /Size " . $size . " /Root 1 0 R /Info 5 0 R >>\nstartxref\n" . $xref . "\n%%EOF\n";

        return $pdf;
    }

    private function footer(int $page, int $pages): string
    {
        $label = 'Page ' . $page . ' / ' . $pages;
        $y = 22.0;
        $width = $this->width($label, false);

        return sprintf(
            "BT 0.45 0.5 0.56 rg /F1 8 Tf %.2F %.2F Td (%s) Tj ET\nBT 0.45 0.5 0.56 rg /F1 8 Tf %.2F %.2F Td (%s) Tj ET\n",
            $this->margin, $y, str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $this->encode($this->title)),
            $this->pageWidth - $this->margin - $width, $y, $label
        );
    }
}
