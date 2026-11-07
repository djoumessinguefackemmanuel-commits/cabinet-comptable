<?php

namespace App\Models;

use App\Core\Model;

class Tax extends Model
{
    /**
     * Récupérer toutes les taxes.
     */
    public static function getAll(): array
    {
        $sql = "
            SELECT
                t.*,
                c.name AS company_name,
                e.name AS exercise_name
            FROM taxes t
            INNER JOIN companies c ON c.id = t.company_id
            LEFT JOIN exercises e ON e.id = t.exercise_id
            ORDER BY t.created_at DESC
        ";

        return self::query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les taxes d'un exercice.
     */
    public static function getByExercise(int $exerciseId): array
    {
        $sql = "
            SELECT *
            FROM taxes
            WHERE exercise_id = :exercise_id
            ORDER BY due_date ASC, id ASC
        ";

        return self::query($sql, [
            'exercise_id' => $exerciseId
        ])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Rechercher une taxe par son identifiant.
     */
    public static function findById(int $id): ?array
    {
        $sql = "
            SELECT *
            FROM taxes
            WHERE id = :id
            LIMIT 1
        ";

        $result = self::query($sql, [
            'id' => $id
        ]);

        return $result->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Enregistrer une taxe.
     */
    public static function create(array $data): int|false
    {
        $sql = "
            INSERT INTO taxes (
                company_id,
                exercise_id,
                name,
                tax_type,
                tax_period,
                tax_rate,
                taxable_amount,
                tax_amount,
                declaration_date,
                due_date,
                payment_date,
                status,
                created_at,
                updated_at
            ) VALUES (
                :company_id,
                :exercise_id,
                :name,
                :tax_type,
                :tax_period,
                :tax_rate,
                :taxable_amount,
                :tax_amount,
                :declaration_date,
                :due_date,
                :payment_date,
                :status,
                NOW(),
                NOW()
            )
        ";

        $success = self::execute($sql, [
            'company_id' => $data['company_id'],
            'exercise_id' => $data['exercise_id'] ?? null,
            'name' => $data['name'],
            'tax_type' => $data['tax_type'],
            'tax_period' => $data['tax_period'] ?? null,
            'tax_rate' => $data['tax_rate'] ?? 0,
            'taxable_amount' => $data['taxable_amount'] ?? 0,
            'tax_amount' => $data['tax_amount'] ?? 0,
            'declaration_date' => $data['declaration_date'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'payment_date' => $data['payment_date'] ?? null,
            'status' => $data['status'] ?? 'pending'
        ]);

        if (!$success) {
            return false;
        }

        return (int) self::lastInsertId();
    }

    /**
     * Modifier une taxe.
     */
    public static function update(int $id, array $data): bool
    {
        $sql = "
            UPDATE taxes
            SET
                name = :name,
                tax_type = :tax_type,
                tax_period = :tax_period,
                tax_rate = :tax_rate,
                taxable_amount = :taxable_amount,
                tax_amount = :tax_amount,
                declaration_date = :declaration_date,
                due_date = :due_date,
                payment_date = :payment_date,
                status = :status,
                updated_at = NOW()
            WHERE id = :id
        ";

        return self::execute($sql, [
            'id' => $id,
            'name' => $data['name'],
            'tax_type' => $data['tax_type'],
            'tax_period' => $data['tax_period'] ?? null,
            'tax_rate' => $data['tax_rate'] ?? 0,
            'taxable_amount' => $data['taxable_amount'] ?? 0,
            'tax_amount' => $data['tax_amount'] ?? 0,
            'declaration_date' => $data['declaration_date'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'payment_date' => $data['payment_date'] ?? null,
            'status' => $data['status']
        ]);
    }

    /**
     * Récupérer les déclarations fiscales.
     */
    public static function getDeclarations(int $exerciseId): array
    {
        $sql = "
            SELECT *
            FROM taxes
            WHERE exercise_id = :exercise_id
            AND declaration_date IS NOT NULL
            ORDER BY declaration_date DESC
        ";

        return self::query($sql, [
            'exercise_id' => $exerciseId
        ])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les échéances fiscales.
     */
    public static function getDeadlines(int $exerciseId): array
    {
        $sql = "
            SELECT *
            FROM taxes
            WHERE exercise_id = :exercise_id
            AND due_date IS NOT NULL
            ORDER BY due_date ASC
        ";

        return self::query($sql, [
            'exercise_id' => $exerciseId
        ])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Supprimer une taxe.
     */
    public static function delete(int $id): bool
    {
        $sql = "
            DELETE FROM taxes
            WHERE id = :id
        ";

        return self::execute($sql, [
            'id' => $id
        ]);
    }

    /**
     * Calculer le total des taxes d'un exercice.
     */
    public static function getTotalByExercise(int $exerciseId): float
    {
        $sql = "
            SELECT COALESCE(SUM(tax_amount), 0) AS total
            FROM taxes
            WHERE exercise_id = :exercise_id
        ";

        $result = self::query($sql, [
            'exercise_id' => $exerciseId
        ]);

        return (float) $result->fetchColumn();
    }
}