<?php

namespace App\Models;

use App\Core\Model;

class Invoice extends Model
{
    /**
     * Récupérer toutes les factures.
     */
    public static function getAll(array $filters = []): array
    {
        $sql = "
            SELECT
                i.*,
                c.name AS company_name,
                CONCAT(u.first_name, ' ', u.last_name) AS user_name
            FROM invoices i
            INNER JOIN companies c ON c.id = i.company_id
            LEFT JOIN users u ON u.id = i.user_id
        ";

        $conditions = [];
        $params = [];

        if (!empty($filters['company_id'])) {
            $conditions[] = 'i.company_id = :company_id';
            $params['company_id'] = (int) $filters['company_id'];
        } elseif (isset($filters['company_ids'])) {
            $companyIds = $filters['company_ids'];
            if ($companyIds === []) {
                return [];
            }

            $placeholders = [];
            foreach (array_values($companyIds) as $index => $companyId) {
                $placeholder = 'company_scope_' . $index;
                $placeholders[] = ':' . $placeholder;
                $params[$placeholder] = (int) $companyId;
            }
            $conditions[] = 'i.company_id IN (' . implode(', ', $placeholders) . ')';
        }

        if (!empty($filters['status'])) {
            $conditions[] = 'i.status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['start_date'])) {
            $conditions[] = 'i.invoice_date >= :start_date';
            $params['start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $conditions[] = 'i.invoice_date <= :end_date';
            $params['end_date'] = $filters['end_date'];
        }

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY i.invoice_date DESC, i.id DESC';

        return self::query($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les factures d'une entreprise.
     */
    public static function getByCompany(int $companyId): array
    {
        $sql = "
            SELECT
                i.*,
                CONCAT(u.first_name, ' ', u.last_name) AS user_name
            FROM invoices i
            LEFT JOIN users u ON u.id = i.user_id
            WHERE i.company_id = :company_id
            ORDER BY i.invoice_date DESC, i.id DESC
        ";

        return self::query($sql, [
            'company_id' => $companyId
        ])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Rechercher une facture par son identifiant.
     */
    public static function getById(int $id): ?array
    {
        $sql = "
            SELECT
                i.*,
                c.name AS company_name,
                CONCAT(u.first_name, ' ', u.last_name) AS user_name
            FROM invoices i
            INNER JOIN companies c ON c.id = i.company_id
            LEFT JOIN users u ON u.id = i.user_id
            WHERE i.id = :id
            LIMIT 1
        ";

        $result = self::query($sql, [
            'id' => $id
        ]);

        return $result->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Créer une facture.
     */
    public static function create(array $data): int|false
    {
        $sql = "
            INSERT INTO invoices (
                company_id,
                exercise_id,
                user_id,
                invoice_number,
                description,
                amount,
                status,
                invoice_date,
                due_date,
                created_at,
                updated_at
            ) VALUES (
                :company_id,
                :exercise_id,
                :user_id,
                :invoice_number,
                :description,
                :amount,
                :status,
                :invoice_date,
                :due_date,
                NOW(),
                NOW()
            )
        ";

        $success = self::execute($sql, [
            'company_id' => $data['company_id'],
            'exercise_id' => $data['exercise_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'invoice_number' => $data['invoice_number'],
            'description' => $data['description'] ?? null,
            'amount' => $data['amount'],
            'status' => $data['status'] ?? INVOICE_DRAFT,
            'invoice_date' => $data['invoice_date'],
            'due_date' => $data['due_date'] ?? null
        ]);

        if (!$success) {
            return false;
        }

        return (int) self::lastInsertId();
    }

    /**
     * Modifier le statut d'une facture.
     */
    public static function updateStatus(int $id, string $status): bool
    {
        $allowedStatuses = [
            INVOICE_DRAFT,
            INVOICE_PENDING,
            INVOICE_PAID,
            INVOICE_CANCELLED
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            return false;
        }

        $sql = "
            UPDATE invoices
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
     * Supprimer une facture.
     */
    public static function delete(int $id): bool
    {
        $sql = "
            DELETE FROM invoices
            WHERE id = :id
        ";

        return self::execute($sql, [
            'id' => $id
        ]);
    }

    /**
     * Compter les factures d'une entreprise.
     */
    public static function countByCompany(int $companyId): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM invoices
            WHERE company_id = :company_id
        ";

        $result = self::query($sql, [
            'company_id' => $companyId
        ]);

        return (int) $result->fetchColumn();
    }
}