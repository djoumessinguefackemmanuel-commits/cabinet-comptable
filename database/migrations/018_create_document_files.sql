-- Stockage des fichiers dans la base (STORAGE_DRIVER=database), MySQL / MariaDB.
-- Cette table est aussi créée automatiquement par App\Services\FileStore.
CREATE TABLE IF NOT EXISTS document_files (
    file_name VARCHAR(255) NOT NULL PRIMARY KEY,
    content LONGBLOB NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
