CREATE TABLE IF NOT EXISTS entry_lines (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entry_id INT UNSIGNED NOT NULL,
    account_id INT UNSIGNED NOT NULL,
    description VARCHAR(255) NULL,
    debit DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    credit DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_entry_lines_entry
        FOREIGN KEY (entry_id)
        REFERENCES entries(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_entry_lines_account
        FOREIGN KEY (account_id)
        REFERENCES accounts(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    INDEX idx_entry_lines_entry_id (entry_id),
    INDEX idx_entry_lines_account_id (account_id)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;