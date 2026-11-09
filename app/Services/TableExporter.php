<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;
use ZipArchive;

/**
 * Export des tableaux affichés par une page (CSV, Excel, PDF).
 *
 * Le moteur lit les tableaux du HTML déjà rendu : l'export contient donc
 * exactement ce que l'utilisateur voit (mêmes filtres, mêmes droits d'accès),
 * sans écrire d'export spécifique pour chaque page. Aucune dépendance externe.
 */
class TableExporter
{
    public const FORMATS = ['csv', 'xlsx', 'pdf'];

    /** En-têtes de colonnes qui ne sont pas des données (boutons d'action). */
    private const IGNORED_HEADERS = ['', 'action', 'actions', 'options'];

    public static function supports(mixed $format): bool
    {
        return is_string($format) && in_array($format, self::FORMATS, true);
    }

    /**
     * Extraire le titre de la page et ses tableaux depuis le HTML rendu.
     *
     * @return array{title: string, tables: array<int, array{title: string, headers: string[], rows: string[][]}>}
     */
    public static function extract(string $html): array
    {
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div id="export-root">' . $html . '</div>');
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($document);

        $title = '';
        foreach ($xpath->query('//h1') as $heading) {
            $title = self::text($heading);
            if ($title !== '') {
                break;
            }
        }

        $tables = [];
        foreach ($xpath->query('//table') as $tableNode) {
            if (!$tableNode instanceof DOMElement) {
                continue;
            }

            $table = self::readTable($tableNode, $xpath);
            if ($table['rows'] !== []) {
                $tables[] = $table;
            }
        }

        return ['title' => $title, 'tables' => $tables];
    }

    /**
     * Envoyer l'export au navigateur et terminer la requête.
     */
    public static function send(string $html, string $format, string $fallbackTitle = 'export', array $meta = []): never
    {
        $data = self::extract($html);
        $title = $data['title'] !== '' ? $data['title'] : $fallbackTitle;

        if ($data['tables'] === []) {
            http_response_code(404);
            header('Content-Type: text/plain; charset=UTF-8');
            echo "Cette page ne contient aucun tableau à exporter.";
            exit;
        }

        $fileName = self::slug($title) . '-' . date('Y-m-d');

        switch ($format) {
            case 'csv':
                $body = self::toCsv($data['tables']);
                $type = 'text/csv; charset=UTF-8';
                $extension = 'csv';
                break;
            case 'xlsx':
                $body = self::toXlsx($data['tables'], $title);
                $type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                $extension = 'xlsx';
                break;
            default:
                $body = self::toPdf($data['tables'], $title, $meta);
                $type = 'application/pdf';
                $extension = 'pdf';
        }

        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: ' . $type);
        header('Content-Disposition: attachment; filename="' . $fileName . '.' . $extension . '"');
        header('Content-Length: ' . strlen($body));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store');
        echo $body;
        exit;
    }

    /* ---------------------------------------------------------------
     * Lecture des tableaux
     * ------------------------------------------------------------- */

    private static function readTable(DOMElement $table, DOMXPath $xpath): array
    {
        $headers = [];
        $rows = [];

        foreach ($xpath->query('.//tr', $table) as $tr) {
            if (!$tr instanceof DOMElement) {
                continue;
            }

            $cells = [];
            $isHeader = false;
            $singleSpan = false;

            foreach ($tr->childNodes as $cell) {
                if (!$cell instanceof DOMElement || !in_array($cell->tagName, ['td', 'th'], true)) {
                    continue;
                }
                if ($cell->tagName === 'th') {
                    $isHeader = true;
                }
                if ((int) $cell->getAttribute('colspan') > 1) {
                    $singleSpan = true;
                }
                $cells[] = self::text($cell);
            }

            if ($cells === []) {
                continue;
            }

            if ($isHeader && $headers === [] && $rows === []) {
                $headers = $cells;
                continue;
            }

            // Ligne « Aucun résultat » : une seule cellule étalée sur tout le tableau.
            if ($singleSpan && count($cells) === 1) {
                continue;
            }

            $rows[] = $cells;
        }

        // Pas d'en-tête explicite : on numérote les colonnes.
        $width = max(array_map('count', array_merge([$headers], $rows)) ?: [0]);
        if ($headers === []) {
            for ($i = 1; $i <= $width; $i++) {
                $headers[] = 'Colonne ' . $i;
            }
        }

        // Retirer les colonnes d'actions (boutons) et harmoniser la largeur.
        $keep = [];
        for ($i = 0; $i < $width; $i++) {
            $label = mb_strtolower(trim($headers[$i] ?? ''));
            if (!in_array($label, self::IGNORED_HEADERS, true)) {
                $keep[] = $i;
            }
        }

        $cleanHeaders = array_map(static fn(int $i): string => $headers[$i] ?? '', $keep);
        $cleanRows = array_map(
            static fn(array $row): array => array_map(static fn(int $i): string => $row[$i] ?? '', $keep),
            $rows
        );

        return [
            'title' => self::tableTitle($table, $xpath),
            'headers' => $cleanHeaders,
            'rows' => $cleanRows,
        ];
    }

    private static function tableTitle(DOMElement $table, DOMXPath $xpath): string
    {
        $node = $xpath->query(
            'ancestor::*[contains(concat(" ", normalize-space(@class), " "), " card ")][1]'
            . '//*[contains(concat(" ", normalize-space(@class), " "), " card-header ")]',
            $table
        )->item(0);

        if (!$node instanceof DOMElement) {
            return '';
        }

        // Chaque élément enfant (titre, badge…) est séparé par un espace.
        $parts = [];
        foreach ($node->childNodes as $child) {
            $part = self::text($child);
            if ($part !== '') {
                $parts[] = $part;
            }
        }

        return implode(' - ', $parts);
    }

    private static function text(\DOMNode $node): string
    {
        $clone = $node->cloneNode(true);

        if ($clone instanceof DOMElement) {
            // Les boutons et formulaires d'une cellule ne sont pas des données.
            $xp = new DOMXPath($clone->ownerDocument);
            foreach ($xp->query('.//script | .//style | .//form | .//button', $clone) as $remove) {
                $remove->parentNode?->removeChild($remove);
            }
        }

        $text = str_replace("\xC2\xA0", ' ', $clone->textContent);
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    private static function slug(string $title): string
    {
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $title);
        $slug = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $slug === false ? $title : $slug) ?? 'export');
        $slug = trim($slug, '-');

        return $slug === '' ? 'export' : substr($slug, 0, 60);
    }

    /* ---------------------------------------------------------------
     * Valeurs numériques (« 120 000,00 FCFA » → 120000.0)
     * ------------------------------------------------------------- */

    public static function toNumber(string $value): ?float
    {
        $value = trim($value);

        if (!preg_match('/^(-?\d[\d\s.,]*)(?:\s*(?:FCFA|F\s?CFA|XAF|XOF|EUR|USD|€|\$))?$/u', $value, $m)) {
            return null;
        }

        $number = preg_replace('/\s+/u', '', $m[1]);

        // Identifiants (n° de téléphone, de compte, codes…) : on ne les convertit pas.
        if (preg_match('/^-?0\d+$/', $number) || preg_match('/^\d{12,}$/', $number)) {
            return null;
        }

        // Séparateur décimal : la dernière virgule ou le dernier point suivi de 1 à 2 chiffres.
        if (preg_match('/^-?\d+([.,]\d+)?$/', $number)) {
            return (float) str_replace(',', '.', $number);
        }
        if (preg_match('/^-?\d{1,3}(\.\d{3})+(,\d+)?$/', $number)) {
            return (float) str_replace(',', '.', str_replace('.', '', $number));
        }
        if (preg_match('/^-?\d{1,3}(,\d{3})+(\.\d+)?$/', $number)) {
            return (float) str_replace(',', '', $number);
        }

        return null;
    }

    /* ---------------------------------------------------------------
     * CSV (séparateur « ; », UTF-8 avec BOM : s'ouvre directement dans Excel)
     * ------------------------------------------------------------- */

    private static function toCsv(array $tables): string
    {
        $lines = [];

        foreach ($tables as $index => $table) {
            if ($index > 0) {
                $lines[] = '';
            }
            if (count($tables) > 1 && $table['title'] !== '') {
                $lines[] = self::csvLine([$table['title']]);
            }
            $lines[] = self::csvLine($table['headers']);
            foreach ($table['rows'] as $row) {
                $lines[] = self::csvLine($row);
            }
        }

        return "\xEF\xBB\xBF" . implode("\r\n", $lines) . "\r\n";
    }

    private static function csvLine(array $cells): string
    {
        return implode(';', array_map(static function (string $cell): string {
            // Protection contre l'injection de formules dans Excel.
            if (preg_match('/^[=@\t\r]/', $cell) || preg_match('/^[+\-][^\d\s.,]/', $cell)) {
                $cell = "'" . $cell;
            }

            return preg_match('/[;"\r\n]/', $cell)
                ? '"' . str_replace('"', '""', $cell) . '"'
                : $cell;
        }, $cells));
    }

    /* ---------------------------------------------------------------
     * Excel (.xlsx) : classeur minimal, un onglet par tableau
     * ------------------------------------------------------------- */

    private static function toXlsx(array $tables, string $title): string
    {
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new ZipArchive();
        if ($path === false || $zip->open($path, ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Impossible de créer le fichier Excel.');
        }

        $sheetNames = [];
        $overrides = '';
        $sheetEntries = '';
        $relationships = '';

        foreach ($tables as $i => $table) {
            $n = $i + 1;
            $fallback = count($tables) > 1 ? 'Tableau ' . $n : $title;
            $name = self::sheetName($table['title'] !== '' ? $table['title'] : $fallback, $sheetNames);
            $sheetNames[] = $name;

            $overrides .= '<Override PartName="/xl/worksheets/sheet' . $n . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
            $sheetEntries .= '<sheet name="' . self::xml($name) . '" sheetId="' . $n . '" r:id="rId' . $n . '"/>';
            $relationships .= '<Relationship Id="rId' . $n . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $n . '.xml"/>';

            $zip->addFromString('xl/worksheets/sheet' . $n . '.xml', self::sheetXml($table));
        }

        $styleId = count($tables) + 1;
        $relationships .= '<Relationship Id="rId' . $styleId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

        $zip->addFromString('[Content_Types].xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . $overrides . '</Types>');

        $zip->addFromString('_rels/.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>');

        $zip->addFromString('xl/workbook.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>' . $sheetEntries . '</sheets></workbook>');

        $zip->addFromString('xl/_rels/workbook.xml.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . $relationships . '</Relationships>');

        // Styles : 0 = normal, 1 = en-tête (gras, fond gris), 2 = nombre « 1 234,56 ».
        $zip->addFromString('xl/styles.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFE8EEF5"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="3">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
            . '<xf numFmtId="4" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            . '</cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>');

        $zip->close();
        $content = (string) file_get_contents($path);
        @unlink($path);

        return $content;
    }

    private static function sheetXml(array $table): string
    {
        $columns = count($table['headers']);
        $widths = array_fill(0, $columns, 8);

        $rows = [];
        $all = array_merge([$table['headers']], $table['rows']);

        foreach ($all as $r => $row) {
            $cells = '';
            foreach ($row as $c => $value) {
                $ref = self::columnName($c) . ($r + 1);
                $widths[$c] = max($widths[$c], min(60, mb_strlen($value) + 2));

                if ($r === 0) {
                    $cells .= '<c r="' . $ref . '" s="1" t="inlineStr"><is><t>' . self::xml($value) . '</t></is></c>';
                    continue;
                }

                $number = self::toNumber($value);
                if ($number !== null) {
                    $style = preg_match('/[.,]\d/', $value) ? 2 : 0;
                    $cells .= '<c r="' . $ref . '" s="' . $style . '"><v>' . rtrim(rtrim(sprintf('%.6F', $number), '0'), '.') . '</v></c>';
                } elseif ($value !== '') {
                    $cells .= '<c r="' . $ref . '" t="inlineStr"><is><t xml:space="preserve">' . self::xml($value) . '</t></is></c>';
                }
            }
            $rows[] = '<row r="' . ($r + 1) . '">' . $cells . '</row>';
        }

        $cols = '';
        foreach ($widths as $i => $width) {
            $cols .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $width . '" customWidth="1"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<cols>' . $cols . '</cols><sheetData>' . implode('', $rows) . '</sheetData>'
            . '<autoFilter ref="A1:' . self::columnName(max(0, $columns - 1)) . max(1, count($all)) . '"/>'
            . '</worksheet>';
    }

    private static function columnName(int $index): string
    {
        $name = '';
        for ($i = $index; $i >= 0; $i = intdiv($i, 26) - 1) {
            $name = chr(65 + $i % 26) . $name;
        }

        return $name;
    }

    private static function sheetName(string $name, array $used): string
    {
        $name = trim(preg_replace('/[\\\\\/\?\*\[\]:]/u', ' ', $name) ?? '');
        $name = mb_substr($name === '' ? 'Feuille' : $name, 0, 31);

        $base = $name;
        $n = 2;
        while (in_array(mb_strtolower($name), array_map('mb_strtolower', $used), true)) {
            $suffix = ' ' . $n++;
            $name = mb_substr($base, 0, 31 - mb_strlen($suffix)) . $suffix;
        }

        return $name;
    }

    private static function xml(string $value): string
    {
        // Retirer les caractères interdits en XML 1.0.
        $value = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $value) ?? '';

        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /* ---------------------------------------------------------------
     * PDF
     * ------------------------------------------------------------- */

    private static function toPdf(array $tables, string $title, array $meta): string
    {
        return (new PdfTable($title, $meta))->render($tables);
    }
}
