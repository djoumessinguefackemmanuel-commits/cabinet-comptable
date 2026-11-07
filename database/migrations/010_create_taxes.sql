CREATE TABLE IF NOT EXISTS taxes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    exercise_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    tax_type VARCHAR(100) NOT NULL,
    tax_period VARCHAR(100) NULL,
    tax_rate DECIMAL(8,4) NOT NULL DEFAULT 0.0000,
    taxable_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    tax_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    declaration_date DATE NULL,
    due_date DATE NULL,
    payment_date DATE NULL,
    status ENUM('pending', 'declared', 'paid', 'overdue')
        NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_taxes_company
        FOREIGN KEY (company_id)
        REFERENCES companies(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_taxes_exercise
        FOREIGN KEY (exercise_id)
        REFERENCES exercises(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    INDEX idx_taxes_company_id (company_id),
    INDEX idx_taxes_exercise_id (exercise_id),
    INDEX idx_taxes_type (tax_type),
    INDEX idx_taxes_status (status),
    INDEX idx_taxes_declaration_date (declaration_date),
    INDEX idx_taxes_due_date (due_date)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;