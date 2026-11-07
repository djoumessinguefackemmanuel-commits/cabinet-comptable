CREATE TABLE IF NOT EXISTS invoices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    exercise_id INT UNSIGNED NULL,
    user_id INT UNSIGNED NULL,
    invoice_number VARCHAR(50) NOT NULL,
    description TEXT NULL,
    amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    status ENUM(
        'draft',
        'pending',
        'paid',
        'cancelled'
    ) NOT NULL DEFAULT 'draft',
    invoice_date DATE NOT NULL,
    due_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_invoices_company
        FOREIGN KEY (company_id)
        REFERENCES companies(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_invoices_exercise
        FOREIGN KEY (exercise_id)
        REFERENCES exercises(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_invoices_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    UNIQUE KEY unique_company_invoice (
        company_id,
        invoice_number
    ),

    INDEX idx_invoices_company_id (company_id),
    INDEX idx_invoices_exercise_id (exercise_id),
    INDEX idx_invoices_user_id (user_id),
    INDEX idx_invoices_status (status),
    INDEX idx_invoices_invoice_date (invoice_date),
    INDEX idx_invoices_due_date (due_date)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;