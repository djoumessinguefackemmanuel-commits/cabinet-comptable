<?php

namespace App\Models;

use App\Core\Model;

class Account extends Model
{
    /**
     * Récupérer tous les comptes comptables.
     */
    public static function getAll(): array
    {
        $sql = "
            SELECT
                a.*,
                c.name AS company_name
            FROM accounts a
            INNER JOIN companies c ON c.id = a.company_id
            ORDER BY a.account_number ASC
        ";

        return self::query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer tous les comptes actifs.
     */
    public static function getAllActive(): array
    {
        $sql = "
            SELECT
                a.*,
                c.name AS company_name
            FROM accounts a
            INNER JOIN companies c ON c.id = a.company_id
            WHERE a.is_active = 1
            ORDER BY a.account_number ASC
        ";

        return self::query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getByCompanies(array $companyIds, bool $activeOnly = false): array
    {
        if ($companyIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($companyIds), '?'));
        $sql = "
            SELECT a.*, c.name AS company_name
            FROM accounts a
            INNER JOIN companies c ON c.id = a.company_id
            WHERE a.company_id IN ({$placeholders})
        ";

        if ($activeOnly) {
            $sql .= ' AND a.is_active = 1';
        }

        $sql .= ' ORDER BY a.account_number ASC';

        return self::query($sql, $companyIds)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les comptes d'une entreprise.
     */
    public static function getByCompany(int $companyId): array
    {
        $sql = "
            SELECT *
            FROM accounts
            WHERE company_id = :company_id
            ORDER BY account_number ASC
        ";

        return self::query($sql, [
            'company_id' => $companyId
        ])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les comptes actifs d'une entreprise.
     */
    public static function getActiveByCompany(int $companyId): array
    {
        $sql = "
            SELECT *
            FROM accounts
            WHERE company_id = :company_id
            AND is_active = 1
            ORDER BY account_number ASC
        ";

        return self::query($sql, [
            'company_id' => $companyId
        ])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Rechercher un compte par son identifiant.
     */
    public static function findById(int $id): ?array
    {
        $sql = "
            SELECT *
            FROM accounts
            WHERE id = :id
            LIMIT 1
        ";

        $result = self::query($sql, [
            'id' => $id
        ]);

        return $result->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Rechercher un compte par son numéro dans une entreprise.
     */
    public static function findByNumber(
        int $companyId,
        string $accountNumber
    ): ?array {
        $sql = "
            SELECT *
            FROM accounts
            WHERE company_id = :company_id
            AND account_number = :account_number
            LIMIT 1
        ";

        $result = self::query($sql, [
            'company_id' => $companyId,
            'account_number' => $accountNumber
        ]);

        return $result->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Créer un compte comptable.
     */
    public static function create(array $data): int|false
    {
        $sql = "
            INSERT INTO accounts (
                company_id,
                account_number,
                name,
                type,
                description,
                is_active,
                created_at,
                updated_at
            ) VALUES (
                :company_id,
                :account_number,
                :name,
                :type,
                :description,
                :is_active,
                NOW(),
                NOW()
            )
        ";

        $success = self::execute($sql, [
            'company_id' => $data['company_id'],
            'account_number' => $data['account_number'],
            'name' => $data['name'],
            'type' => $data['type'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? 1
        ]);

        if (!$success) {
            return false;
        }

        return (int) self::lastInsertId();
    }

    /**
     * Modifier un compte comptable.
     */
    public static function update(int $id, array $data): bool
    {
        $sql = "
            UPDATE accounts
            SET
                account_number = :account_number,
                name = :name,
                type = :type,
                description = :description,
                updated_at = NOW()
            WHERE id = :id
        ";

        return self::execute($sql, [
            'id' => $id,
            'account_number' => $data['account_number'],
            'name' => $data['name'],
            'type' => $data['type'],
            'description' => $data['description'] ?? null
        ]);
    }

    /**
     * Modifier l'état actif ou inactif d'un compte.
     */
    public static function updateStatus(int $id, int $isActive): bool
    {
        $sql = "
            UPDATE accounts
            SET
                is_active = :is_active,
                updated_at = NOW()
            WHERE id = :id
        ";

        return self::execute($sql, [
            'id' => $id,
            'is_active' => $isActive ? 1 : 0
        ]);
    }

    /**
     * Supprimer un compte comptable.
     */
    public static function delete(int $id): bool
    {
        $sql = "
            DELETE FROM accounts
            WHERE id = :id
        ";

        return self::execute($sql, [
            'id' => $id
        ]);
    }

    /**
     * Compter les comptes d'une entreprise.
     */
    public static function countByCompany(int $companyId): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM accounts
            WHERE company_id = :company_id
        ";

        $result = self::query($sql, [
            'company_id' => $companyId
        ]);

        return (int) $result->fetchColumn();
    }

    /**
     * Type de compte déduit du numéro SYSCOHADA.
     */
    public static function typeFromNumber(string $number): string
    {
        $n2 = (int) substr($number, 0, 2);
        $c = (int) substr($number, 0, 1);

        return match (true) {
            $c === 1 && $n2 <= 15 => ACCOUNT_EQUITY,
            $c === 1 => ACCOUNT_LIABILITY,
            $c === 2 || $c === 3 => ACCOUNT_ASSET,
            $c === 4 => self::isReceivable($number) ? ACCOUNT_ASSET : ACCOUNT_LIABILITY,
            $c === 5 => $n2 === 56 ? ACCOUNT_LIABILITY : ACCOUNT_ASSET,
            $c === 6 => ACCOUNT_EXPENSE,
            $c === 7 => ACCOUNT_REVENUE,
            default => in_array($n2, [82, 84, 86, 88], true) ? ACCOUNT_REVENUE : ACCOUNT_EXPENSE,
        };
    }

    private static function isReceivable(string $number): bool
    {
        foreach (['409', '4449', '445', '476', '478', '49'] as $p) {
            if (str_starts_with($number, $p)) {
                return true;
            }
        }

        return str_starts_with($number, '41') && !str_starts_with($number, '419');
    }

    /**
     * Importer le plan SYSCOHADA dans une entreprise (les comptes déjà présents sont conservés).
     *
     * @return int Nombre de comptes ajoutés
     */
    public static function importSyscohada(int $companyId, array $chart): int
    {
        $existing = [];
        foreach (self::getByCompany($companyId) as $row) {
            $existing[(string) $row['account_number']] = true;
        }

        $added = 0;
        self::beginTransaction();
        try {
            foreach ($chart as $number => $name) {
                $number = (string) $number;
                if (isset($existing[$number])) {
                    continue;
                }
                self::execute(
                    'INSERT INTO accounts (company_id, account_number, name, type, is_active) VALUES (:c, :n, :l, :t, 1)',
                    ['c' => $companyId, 'n' => $number, 'l' => $name, 't' => self::typeFromNumber($number)]
                );
                $added++;
            }
            self::commit();
        } catch (\Throwable $e) {
            self::rollback();
            throw $e;
        }

        return $added;
    }
}