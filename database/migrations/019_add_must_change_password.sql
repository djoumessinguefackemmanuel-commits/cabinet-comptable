-- MySQL : l'application crée aussi cette colonne automatiquement si elle est absente.
ALTER TABLE users ADD COLUMN must_change_password TINYINT NOT NULL DEFAULT 0;
