<?php

namespace App\Models;

use App\Core\Model;

class Document extends Model
{
    /**
     * Récupérer tous les documents.
     */
    public static function getAll(): array
    {
        $sql = "
            SELECT
                d.*,
                c.name AS company_name,
                CONCAT(u.first_name, ' ', u.last_name) AS user_name
            FROM documents d
            LEFT JOIN companies c ON c.id = d.company_id
            LEFT JOIN users u ON u.id = d.user_id
            ORDER BY d.created_at DESC
        ";

        return self::query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les documents d'une entreprise.
     */
    public static function getByCompany(int $companyId): array
    {
        $sql = "
            SELECT
                d.*,
                CONCAT(u.first_name, ' ', u.last_name) AS user_name
            FROM documents d
            LEFT JOIN users u ON u.id = d.user_id
            WHERE d.company_id = :company_id
            ORDER BY d.created_at DESC
        ";

        return self::query($sql, [
            'company_id' => $companyId
        ])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les documents déposés par un utilisateur.
     */
    public static function getByUser(?int $userId): array
    {
        if ($userId === null) {
            return [];
        }

        $sql = "
            SELECT
                d.*,
                c.name AS company_name,
                CONCAT(u.first_name, ' ', u.last_name) AS user_name
            FROM documents d
            LEFT JOIN companies c ON c.id = d.company_id
            LEFT JOIN users u ON u.id = d.user_id
            WHERE d.user_id = :user_id
            ORDER BY d.created_at DESC
        ";

        return self::query($sql, [
            'user_id' => $userId
        ])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Rechercher un document par son identifiant.
     */
    public static function findById(int $id): ?array
    {
        $sql = "
            SELECT *
            FROM documents
            WHERE id = :id
            LIMIT 1
        ";

        $result = self::query($sql, [
            'id' => $id
        ]);

        return $result->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Enregistrer un document.
     */
    public static function create(array $data): int|false
    {
        $sql = "
            INSERT INTO documents (
                user_id,
                company_id,
                exercise_id,
                name,
                file_name,
                file_path,
                file_type,
                file_size,
                created_at
            ) VALUES (
                :user_id,
                :company_id,
                :exercise_id,
                :name,
                :file_name,
                :file_path,
                :file_type,
                :file_size,
                NOW()
            )
        ";

        $success = self::execute($sql, [
            'user_id' => $data['user_id'],
            'company_id' => $data['company_id'] ?? null,
            'exercise_id' => $data['exercise_id'] ?? null,
            'name' => $data['name'],
            'file_name' => $data['file_name'],
            'file_path' => $data['file_path'],
            'file_type' => $data['file_type'],
            'file_size' => $data['file_size']
        ]);

        if (!$success) {
            return false;
        }

        return (int) self::lastInsertId();
    }

    /**
     * Modifier les informations d'un document.
     */
    public static function update(int $id, array $data): bool
    {
        $sql = "
            UPDATE documents
            SET
                name = :name,
                file_name = :file_name,
                file_path = :file_path,
                file_type = :file_type,
                file_size = :file_size
            WHERE id = :id
        ";

        return self::execute($sql, [
            'id' => $id,
            'name' => $data['name'],
            'file_name' => $data['file_name'],
            'file_path' => $data['file_path'],
            'file_type' => $data['file_type'],
            'file_size' => $data['file_size']
        ]);
    }

    /**
     * Supprimer un document de la base de données.
     */
    public static function delete(int $id): bool
    {
        $sql = "
            DELETE FROM documents
            WHERE id = :id
        ";

        return self::execute($sql, [
            'id' => $id
        ]);
    }

    /**
     * Compter les documents d'une entreprise.
     */
    public static function countByCompany(int $companyId): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM documents
            WHERE company_id = :company_id
        ";

        $result = self::query($sql, [
            'company_id' => $companyId
        ]);

        return (int) $result->fetchColumn();
    }
}