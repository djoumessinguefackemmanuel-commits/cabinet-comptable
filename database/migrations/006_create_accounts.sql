CREATE TABLE IF NOT EXISTS accounts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    exercise_id INT UNSIGNED NULL,
    account_number VARCHAR(20) NOT NULL,
    name VARCHAR(150) NOT NULL,
    type ENUM(
        'asset',
        'liability',
        'equity',
        'expense',
        'revenue'
    ) NOT NULL,
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_accounts_company
        FOREIGN KEY (company_id)
        REFERENCES companies(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_accounts_exercise
        FOREIGN KEY (exercise_id)
        REFERENCES exercises(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    UNIQUE KEY unique_company_account (
        company_id,
        account_number
    ),

    INDEX idx_accounts_company_id (company_id),
    INDEX idx_accounts_exercise_id (exercise_id),
    INDEX idx_accounts_type (type),
    INDEX idx_accounts_active (is_active)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;