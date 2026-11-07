CREATE TABLE IF NOT EXISTS journals (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    exercise_id INT UNSIGNED NULL,
    code VARCHAR(20) NOT NULL,
    name VARCHAR(150) NOT NULL,
    type ENUM(
        'sales',
        'purchases',
        'cash',
        'bank',
        'miscellaneous'
    ) NOT NULL,
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_journals_company
        FOREIGN KEY (company_id)
        REFERENCES companies(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_journals_exercise
        FOREIGN KEY (exercise_id)
        REFERENCES exercises(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    UNIQUE KEY unique_company_journal (
        company_id,
        code
    ),

    INDEX idx_journals_company_id (company_id),
    INDEX idx_journals_exercise_id (exercise_id),
    INDEX idx_journals_type (type),
    INDEX idx_journals_active (is_active)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;