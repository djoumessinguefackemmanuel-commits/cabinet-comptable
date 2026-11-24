<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;
use PDO;

class FixedAsset extends Model
{
    /** Catégories : compte d'immobilisation, compte d'amortissement, durée usuelle (ans). */
    public const CATEGORIES = [
        'logiciel' => ['label' => 'Logiciels, brevets, licences', 'asset' => '212', 'amort' => '2812', 'years' => 3],
        'batiment' => ['label' => 'Bâtiments', 'asset' => '231', 'amort' => '2831', 'years' => 20],
        'materiel' => ['label' => 'Matériel et outillage', 'asset' => '241', 'amort' => '2841', 'years' => 10],
        'mobilier' => ['label' => 'Mobilier et matériel de bureau', 'asset' => '244', 'amort' => '2844', 'years' => 10],
        'transport' => ['label' => 'Matériel de transport', 'asset' => '245', 'amort' => '2845', 'years' => 5],
        'informatique' => ['label' => 'Matériel informatique', 'asset' => '246', 'amort' => '2846', 'years' => 3],
    ];

    private static bool $ready = false;

    public static function ensureSchema(): void
    {
        if (self::$ready) {
            return;
        }

        $pdo = Database::connect();
        $pg = defined('DB_DRIVER') && DB_DRIVER === 'pgsql';
        $id = $pg ? 'SERIAL PRIMARY KEY' : 'INT AUTO_INCREMENT PRIMARY KEY';

        try {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS fixed_assets (
                    id $id,
                    company_id INT NOT NULL,
                    reference VARCHAR(30) NOT NULL,
                    name VARCHAR(200) NOT NULL,
                    category VARCHAR(30) NOT NULL,
                    acquisition_date DATE NOT NULL,
                    cost DECIMAL(15,2) NOT NULL,
                    residual_value DECIMAL(15,2) NOT NULL DEFAULT 0,
                    useful_life_years INT NOT NULL,
                    disposal_date DATE NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    CONSTRAINT uq_fa_ref UNIQUE (company_id, reference),
                    CONSTRAINT fk_fa_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
                )"
            );
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS fixed_asset_postings (
                    id $id,
                    asset_id INT NOT NULL,
                    exercise_id INT NOT NULL,
                    entry_id INT NULL,
                    amount DECIMAL(15,2) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    CONSTRAINT uq_fap UNIQUE (asset_id, exercise_id),
                    CONSTRAINT fk_fap_asset FOREIGN KEY (asset_id) REFERENCES fixed_assets(id) ON DELETE CASCADE
                )"
            );
        } catch (\Throwable $e) {
            error_log('fixed_assets schema : ' . $e->getMessage());
        }

        self::$ready = true;
    }

    public static function companies(?array $ids): array
    {
        if ($ids === []) {
            return [];
        }
        $where = $ids === null ? '' : 'WHERE id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')';

        return self::query("SELECT id, name FROM companies $where ORDER BY name", $ids === null ? [] : array_values($ids))->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function allFor(?array $ids): array
    {
        self::ensureSchema();
        if ($ids === []) {
            return [];
        }
        $where = $ids === null ? '' : 'WHERE a.company_id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')';

        return self::query(
            "SELECT a.*, c.name AS company_name FROM fixed_assets a INNER JOIN companies c ON c.id = a.company_id $where
             ORDER BY c.name, a.acquisition_date DESC, a.id DESC",
            $ids === null ? [] : array_values($ids)
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array
    {
        self::ensureSchema();

        return self::query(
            'SELECT a.*, c.name AS company_name FROM fixed_assets a INNER JOIN companies c ON c.id = a.company_id WHERE a.id = ?',
            [$id]
        )->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function activeByCompany(int $companyId): array
    {
        self::ensureSchema();

        return self::query('SELECT * FROM fixed_assets WHERE company_id = ? ORDER BY id', [$companyId])->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function nextReference(int $companyId): string
    {
        $n = (int) self::query('SELECT COUNT(*) FROM fixed_assets WHERE company_id = ?', [$companyId])->fetchColumn();
        do {
            $ref = sprintf('IMM-%04d', ++$n);
            $exists = self::query('SELECT 1 FROM fixed_assets WHERE company_id = ? AND reference = ?', [$companyId, $ref])->fetchColumn();
        } while ($exists);

        return $ref;
    }

    public static function create(array $d): int|false
    {
        self::ensureSchema();
        $ok = self::execute(
            'INSERT INTO fixed_assets (company_id, reference, name, category, acquisition_date, cost, residual_value, useful_life_years)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [$d['company_id'], $d['reference'], $d['name'], $d['category'], $d['acquisition_date'], $d['cost'], $d['residual_value'], $d['useful_life_years']]
        );

        return $ok ? (int) self::lastInsertId() : false;
    }

    public static function dispose(int $id, string $date): bool
    {
        return self::execute('UPDATE fixed_assets SET disposal_date = ? WHERE id = ?', [$date, $id]);
    }

    public static function postings(int $assetId): array
    {
        self::ensureSchema();

        return self::query(
            'SELECT p.*, e.reference AS entry_reference, x.name AS exercise_name
             FROM fixed_asset_postings p
             LEFT JOIN entries e ON e.id = p.entry_id
             LEFT JOIN exercises x ON x.id = p.exercise_id
             WHERE p.asset_id = ? ORDER BY p.id',
            [$assetId]
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function hasPostings(int $assetId): bool
    {
        self::ensureSchema();

        return (bool) self::query('SELECT 1 FROM fixed_asset_postings WHERE asset_id = ?', [$assetId])->fetchColumn();
    }

    public static function postedExerciseIds(int $assetId): array
    {
        return array_map('intval', self::query('SELECT exercise_id FROM fixed_asset_postings WHERE asset_id = ?', [$assetId])->fetchAll(PDO::FETCH_COLUMN));
    }

    public static function recordPosting(int $assetId, int $exerciseId, int $entryId, float $amount): void
    {
        self::execute('INSERT INTO fixed_asset_postings (asset_id, exercise_id, entry_id, amount) VALUES (?, ?, ?, ?)', [$assetId, $exerciseId, $entryId, $amount]);
    }

    public static function delete(int $id): bool
    {
        return !self::hasPostings($id) && self::execute('DELETE FROM fixed_assets WHERE id = ?', [$id]);
    }
}
