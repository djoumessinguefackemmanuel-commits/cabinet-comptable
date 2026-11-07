<?php
class ExportService
{
    /**
     * Exporter les écritures comptables au format CSV.
     */
    public static function exportEntries(int $exerciseId): string|false
    {
        try {
            $sql = "
                SELECT
                    e.entry_number,
                    e.entry_date,
                    e.description,
                    j.code AS journal_code,
                    a.account_number,
                    a.label AS account_label,
                    el.description AS line_description,
                    el.debit,
                    el.credit
                FROM entries e
                INNER JOIN journals j ON j.id = e.journal_id
                INNER JOIN entry_lines el ON el.entry_id = e.id
                INNER JOIN accounts a ON a.id = el.account_id
                WHERE e.exercise_id = :exercise_id
                ORDER BY e.entry_date ASC, e.id ASC, el.id ASC
            ";

            $stmt = Database::getConnection()->prepare($sql);
            $stmt->execute([':exercise_id' => $exerciseId]);

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return self::generateCsv(
                'ecritures_comptables',
                [
                    'Numéro écriture',
                    'Date',
                    'Description',
                    'Journal',
                    'Numéro compte',
                    'Intitulé compte',
                    'Libellé ligne',
                    'Débit',
                    'Crédit'
                ],
                $rows
            );

        } catch (PDOException $e) {
            error_log('Erreur ExportService::exportEntries : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Exporter la balance comptable au format CSV.
     */
    public static function exportBalance(int $exerciseId): string|false
    {
        try {
            $sql = "
                SELECT
                    a.account_number,
                    a.label AS account_label,
                    COALESCE(SUM(el.debit), 0) AS total_debit,
                    COALESCE(SUM(el.credit), 0) AS total_credit,
                    COALESCE(SUM(el.debit - el.credit), 0) AS balance
                FROM accounts a
                LEFT JOIN entry_lines el ON el.account_id = a.id
                LEFT JOIN entries e
                    ON e.id = el.entry_id
                    AND e.exercise_id = :exercise_id
                    AND e.status = :status
                GROUP BY a.id, a.account_number, a.label
                ORDER BY a.account_number ASC
            ";

            $stmt = Database::getConnection()->prepare($sql);

            $stmt->execute([
                ':exercise_id' => $exerciseId,
                ':status' => ENTRY_VALIDATED
            ]);

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return self::generateCsv(
                'balance_comptable',
                [
                    'Numéro compte',
                    'Intitulé compte',
                    'Total débit',
                    'Total crédit',
                    'Solde'
                ],
                $rows
            );

        } catch (PDOException $e) {
            error_log('Erreur ExportService::exportBalance : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Générer un fichier CSV et retourner son chemin.
     */
    private static function generateCsv(
        string $fileName,
        array $headers,
        array $rows
    ): string|false {
        try {
            $directory = STORAGE_PATH . '/exports';

            if (!is_dir($directory)) {
                if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
                    return false;
                }
            }

            $filePath = $directory . '/' . $fileName . '_' . date('Ymd_His') . '.csv';

            $file = fopen($filePath, 'w');

            if ($file === false) {
                return false;
            }

            // BOM UTF-8 pour une bonne lecture dans Excel.
            fwrite($file, "\xEF\xBB\xBF");

            // Écrire les en-têtes.
            fputcsv($file, $headers, ';');

            // Écrire les données.
            foreach ($rows as $row) {
                fputcsv($file, array_values($row), ';');
            }

            fclose($file);

            return $filePath;

        } catch (Throwable $e) {
            error_log('Erreur ExportService::generateCsv : ' . $e->getMessage());
            return false;
        }
    }
}
