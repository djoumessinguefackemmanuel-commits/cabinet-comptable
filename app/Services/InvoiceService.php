<?php

class InvoiceService
{
    /**
     * Créer une nouvelle facture.
     */
    public static function create(array $data): int|false
    {
        try {
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
                    created_at
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
                    NOW()
                )
            ";

            $stmt = Database::getConnection()->prepare($sql);

            $stmt->execute([
                ':company_id' => $data['company_id'],
                ':exercise_id' => $data['exercise_id'] ?? null,
                ':user_id' => $data['user_id'] ?? Auth::id(),
                ':invoice_number' => $data['invoice_number'],
                ':description' => $data['description'] ?? null,
                ':amount' => $data['amount'],
                ':status' => $data['status'] ?? INVOICE_DRAFT,
                ':invoice_date' => $data['invoice_date'],
                ':due_date' => $data['due_date'] ?? null
            ]);

            return (int) Database::getConnection()->lastInsertId();

        } catch (PDOException $e) {
            error_log('Erreur InvoiceService::create : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer une facture par son identifiant.
     */
    public static function getById(int $invoiceId): ?array
    {
        try {
            $sql = "
                SELECT
                    invoices.*,
                    companies.name AS company_name,
                    users.first_name,
                    users.last_name
                FROM invoices
                LEFT JOIN companies ON companies.id = invoices.company_id
                LEFT JOIN users ON users.id = invoices.user_id
                WHERE invoices.id = :id
                LIMIT 1
            ";

            $stmt = Database::getConnection()->prepare($sql);
            $stmt->execute([':id' => $invoiceId]);

            $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

            return $invoice ?: null;

        } catch (PDOException $e) {
            error_log('Erreur InvoiceService::getById : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupérer les factures d'une entreprise.
     */
    public static function getByCompany(int $companyId): array
    {
        try {
            $sql = "
                SELECT *
                FROM invoices
                WHERE company_id = :company_id
                ORDER BY invoice_date DESC, id DESC
            ";

            $stmt = Database::getConnection()->prepare($sql);
            $stmt->execute([':company_id' => $companyId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log('Erreur InvoiceService::getByCompany : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Modifier le statut d'une facture.
     */
    public static function updateStatus(int $invoiceId, string $status): bool
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

        try {
            $sql = "
                UPDATE invoices
                SET status = :status,
                    updated_at = NOW()
                WHERE id = :id
            ";

            $stmt = Database::getConnection()->prepare($sql);

            return $stmt->execute([
                ':status' => $status,
                ':id' => $invoiceId
            ]);

        } catch (PDOException $e) {
            error_log('Erreur InvoiceService::updateStatus : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer toutes les factures.
     */
    public static function getAll(): array
    {
        try {
            $sql = "
                SELECT
                    invoices.*,
                    companies.name AS company_name,
                    users.first_name,
                    users.last_name
                FROM invoices
                LEFT JOIN companies ON companies.id = invoices.company_id
                LEFT JOIN users ON users.id = invoices.user_id
                ORDER BY invoices.created_at DESC
            ";

            $stmt = Database::getConnection()->query($sql);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log('Erreur InvoiceService::getAll : ' . $e->getMessage());
            return [];
        }
    }
}
