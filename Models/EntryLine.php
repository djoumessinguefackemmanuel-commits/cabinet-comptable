<?php

namespace App\Models;

use App\Core\Model;

class EntryLine extends Model
{
    /**
     * Récupérer toutes les lignes d'une écriture.
     */
    public static function getByEntry(int $entryId): array
    {
        $sql = "
            SELECT
                el.*,
                a.account_number,
                a.name AS account_name
            FROM entry_lines el
            INNER JOIN accounts a ON a.id = el.account_id
            WHERE el.entry_id = :entry_id
            ORDER BY el.id ASC
        ";

        return self::query($sql, [
            'entry_id' => $entryId
        ])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Rechercher une ligne par son identifiant.
     */
    public static function findById(int $id): ?array
    {
        $sql = "
            SELECT
                el.*,
                a.account_number,
                a.name AS account_name
            FROM entry_lines el
            INNER JOIN accounts a ON a.id = el.account_id
            WHERE el.id = :id
            LIMIT 1
        ";

        $result = self::query($sql, [
            'id' => $id
        ]);

        return $result->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Créer une ligne comptable.
     */
    public static function create(array $data): int|false
    {
        $sql = "
            INSERT INTO entry_lines (
                entry_id,
                account_id,
                description,
                debit,
                credit,
                created_at
            ) VALUES (
                :entry_id,
                :account_id,
                :description,
                :debit,
                :credit,
                NOW()
            )
        ";

        $success = self::execute($sql, [
            'entry_id' => $data['entry_id'],
            'account_id' => $data['account_id'],
            'description' => $data['description'] ?? null,
            'debit' => $data['debit'] ?? 0,
            'credit' => $data['credit'] ?? 0
        ]);

        if (!$success) {
            return false;
        }

        return (int) self::lastInsertId();
    }

    /**
     * Modifier une ligne comptable.
     */
    public static function update(int $id, array $data): bool
    {
        $sql = "
            UPDATE entry_lines
            SET
                account_id = :account_id,
                description = :description,
                debit = :debit,
                credit = :credit
            WHERE id = :id
        ";

        return self::execute($sql, [
            'id' => $id,
            'account_id' => $data['account_id'],
            'description' => $data['description'] ?? null,
            'debit' => $data['debit'] ?? 0,
            'credit' => $data['credit'] ?? 0
        ]);
    }

    /**
     * Supprimer une ligne comptable.
     */
    public static function delete(int $id): bool
    {
        $sql = "
            DELETE FROM entry_lines
            WHERE id = :id
        ";

        return self::execute($sql, [
            'id' => $id
        ]);
    }

    /**
     * Supprimer toutes les lignes d'une écriture.
     */
    public static function deleteByEntry(int $entryId): bool
    {
        $sql = "
            DELETE FROM entry_lines
            WHERE entry_id = :entry_id
        ";

        return self::execute($sql, [
            'entry_id' => $entryId
        ]);
    }

    /**
     * Calculer le total des débits d'une écriture.
     */
    public static function getTotalDebit(int $entryId): float
    {
        $sql = "
            SELECT COALESCE(SUM(debit), 0) AS total
            FROM entry_lines
            WHERE entry_id = :entry_id
        ";

        $result = self::query($sql, [
            'entry_id' => $entryId
        ]);

        return (float) $result->fetchColumn();
    }

    /**
     * Calculer le total des crédits d'une écriture.
     */
    public static function getTotalCredit(int $entryId): float
    {
        $sql = "
            SELECT COALESCE(SUM(credit), 0) AS total
            FROM entry_lines
            WHERE entry_id = :entry_id
        ";

        $result = self::query($sql, [
            'entry_id' => $entryId
        ]);

        return (float) $result->fetchColumn();
    }
}