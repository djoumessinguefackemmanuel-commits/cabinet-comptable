-- Cabinets clients (multi-tenant). MySQL : l'application applique aussi ces changements automatiquement.
CREATE TABLE IF NOT EXISTS tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
ALTER TABLE users ADD COLUMN tenant_id INT NULL;
ALTER TABLE companies ADD COLUMN tenant_id INT NULL;
INSERT INTO tenants (name) SELECT 'Cabinet principal' WHERE NOT EXISTS (SELECT 1 FROM tenants);
UPDATE companies SET tenant_id = (SELECT MIN(id) FROM tenants) WHERE tenant_id IS NULL;
UPDATE users SET tenant_id = (SELECT MIN(id) FROM tenants)
 WHERE tenant_id IS NULL
   AND id <> (SELECT MIN(x.id) FROM (SELECT u.id FROM users u JOIN roles r ON r.id = u.role_id WHERE r.name = 'admin') x);
