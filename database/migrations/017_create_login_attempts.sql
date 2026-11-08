-- Limitation des tentatives de connexion (MySQL / MariaDB).
-- Cette table est aussi créée automatiquement par App\Services\LoginThrottle.
CREATE TABLE IF NOT EXISTS login_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email_hash CHAR(64) NOT NULL,
    ip_hash CHAR(64) NOT NULL,
    attempted_at DATETIME NOT NULL,
    INDEX idx_login_attempts_email (email_hash, attempted_at),
    INDEX idx_login_attempts_ip (ip_hash, attempted_at)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
