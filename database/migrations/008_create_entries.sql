CREATE TABLE IF NOT EXISTS entries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    exercise_id INT UNSIGNED NOT NULL,
    journal_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NULL,
    reference VARCHAR(50) NOT NULL,
    entry_date DATE NOT NULL,
    description TEXT NOT NULL,
    status ENUM('draft', 'validated', 'cancelled')
        NOT NULL DEFAULT 'draft',
    validated_by INT UNSIGNED NULL,
    validated_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_entries_company
        FOREIGN KEY (company_id)
        REFERENCES companies(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_entries_exercise
        FOREIGN KEY (exercise_id)
        REFERENCES exercises(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_entries_journal
        FOREIGN KEY (journal_id)
        REFERENCES journals(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_entries_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_entries_validator
        FOREIGN KEY (validated_by)
        REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    UNIQUE KEY unique_company_reference (company_id, reference),
    INDEX idx_entries_exercise_id (exercise_id),
    INDEX idx_entries_journal_id (journal_id),
    INDEX idx_entries_user_id (user_id),
    INDEX idx_entries_date (entry_date),
    INDEX idx_entries_status (status)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;