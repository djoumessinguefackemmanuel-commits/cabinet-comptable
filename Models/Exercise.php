<?php

namespace App\Models;

use App\Core\Model;

class Exercise extends Model
{
    /**
     * Récupérer tous les exercices comptables.
     */
    public static function getAll(): array
    {
        $sql = "
            SELECT
                e.*,
                c.name AS company_name
            FROM exercises e
            INNER JOIN companies c ON c.id = e.company_id
            ORDER BY e.start_date DESC
        ";

        return self::query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer tous les exercices ouverts.
     */
    public static function getAllOpen(): array
    {
        $sql = "
            SELECT
                e.*,
                c.name AS company_name
            FROM exercises e
            INNER JOIN companies c ON c.id = e.company_id
            WHERE e.status = :status
            ORDER BY e.start_date DESC
        ";

        return self::query($sql, [
            'status' => EXERCISE_OPEN
        ])->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getByCompanies(array $companyIds, bool $openOnly = false): array
    {
        if ($companyIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($companyIds), '?'));
        $sql = "
            SELECT e.*, c.name AS company_name
            FROM exercises e
            INNER JOIN companies c ON c.id = e.company_id
            WHERE e.company_id IN ({$placeholders})
        ";

        if ($openOnly) {
            $sql .= ' AND e.status = ?';
            $companyIds[] = EXERCISE_OPEN;
        }

        $sql .= ' ORDER BY e.start_date DESC';

        return self::query($sql, $companyIds)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les exercices d'une entreprise.
     */
    public static function getByCompany(int $companyId): array
    {
        $sql = "
            SELECT *
            FROM exercises
            WHERE company_id = :company_id
            ORDER BY start_date DESC
        ";

        return self::query($sql, [
            'company_id' => $companyId
        ])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Rechercher un exercice par son identifiant.
     */
    public static function findById(int $id): ?array
    {
        $sql = "
            SELECT
                e.*,
                c.name AS company_name
            FROM exercises e
            INNER JOIN companies c ON c.id = e.company_id
            WHERE e.id = :id
            LIMIT 1
        ";

        $result = self::query($sql, [
            'id' => $id
        ]);

        return $result->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Récupérer l'exercice ouvert d'une entreprise.
     */
    public static function getOpenByCompany(int $companyId): ?array
    {
        $sql = "
            SELECT *
            FROM exercises
            WHERE company_id = :company_id
            AND status = :status
            ORDER BY start_date DESC
            LIMIT 1
        ";

        $result = self::query($sql, [
            'company_id' => $companyId,
            'status' => EXERCISE_OPEN
        ]);

        return $result->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Créer un exercice comptable.
     */
    public static function create(array $data): int|false
    {
        $sql = "
            INSERT INTO exercises (
                company_id,
                name,
                start_date,
                end_date,
                status,
                created_at,
                updated_at
            ) VALUES (
                :company_id,
                :name,
                :start_date,
                :end_date,
                :status,
                NOW(),
                NOW()
            )
        ";

        $success = self::execute($sql, [
            'company_id' => $data['company_id'],
            'name' => $data['name'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => $data['status'] ?? EXERCISE_OPEN
        ]);

        if (!$success) {
            return false;
        }

        return (int) self::lastInsertId();
    }

    /**
     * Modifier un exercice comptable.
     */
    public static function update(int $id, array $data): bool
    {
        $sql = "
            UPDATE exercises
            SET
                company_id = :company_id,
                name = :name,
                start_date = :start_date,
                end_date = :end_date,
                updated_at = NOW()
            WHERE id = :id
        ";

        return self::execute($sql, [
            'id' => $id,
            'company_id' => $data['company_id'],
            'name' => $data['name'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date']
        ]);
    }

    /**
     * Modifier le statut d'un exercice.
     */
    public static function updateStatus(int $id, string $status): bool
    {
        $allowedStatuses = [
            EXERCISE_OPEN,
            EXERCISE_CLOSED,
            EXERCISE_LOCKED
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            return false;
        }

        $sql = "
            UPDATE exercises
            SET
                status = :status,
                updated_at = NOW()
            WHERE id = :id
        ";

        return self::execute($sql, [
            'id' => $id,
            'status' => $status
        ]);
    }

    /**
     * Supprimer un exercice comptable.
     */
    public static function delete(int $id): bool
    {
        $sql = "
            DELETE FROM exercises
            WHERE id = :id
        ";

        return self::execute($sql, [
            'id' => $id
        ]);
    }

    /**
     * Compter les exercices comptables.
     */
    public static function countAll(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM exercises";

        $result = self::query($sql);

        return (int) $result->fetchColumn();
    }
}