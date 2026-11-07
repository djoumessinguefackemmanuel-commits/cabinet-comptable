-- Schéma PostgreSQL pour Neon, généré à partir de database/migrations/*.sql
-- À exécuter une seule fois (éditeur SQL de la console Neon).

CREATE TABLE IF NOT EXISTS roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    role_id INTEGER NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    password VARCHAR(255) NOT NULL,
    status VARCHAR(50) CHECK (status IN ('active', 'inactive', 'suspended')) NOT NULL DEFAULT 'active',
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id) ON UPDATE CASCADE ON DELETE RESTRICT
);
CREATE INDEX IF NOT EXISTS idx_users_role_id ON users (role_id);
CREATE INDEX IF NOT EXISTS idx_users_status ON users (status);

CREATE TABLE IF NOT EXISTS companies (
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(30) NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    tax_number VARCHAR(100) NULL,
    registration_number VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_companies_name ON companies (name);
CREATE INDEX IF NOT EXISTS idx_companies_tax_number ON companies (tax_number);

CREATE TABLE IF NOT EXISTS settings (
    id SERIAL PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS support_requests (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NULL,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(50) CHECK (status IN ('new', 'in_progress', 'resolved')) NOT NULL DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_support_requests_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS idx_support_requests_status ON support_requests (status);
CREATE INDEX IF NOT EXISTS idx_support_requests_created_at ON support_requests (created_at);


CREATE TABLE IF NOT EXISTS user_companies (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    company_id INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_companies_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_user_companies_company FOREIGN KEY (company_id) REFERENCES companies(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT unique_user_company UNIQUE (user_id, company_id)
);
CREATE INDEX IF NOT EXISTS idx_user_companies_user_id ON user_companies (user_id);
CREATE INDEX IF NOT EXISTS idx_user_companies_company_id ON user_companies (company_id);

CREATE TABLE IF NOT EXISTS exercises (
    id SERIAL PRIMARY KEY,
    company_id INTEGER NOT NULL,
    name VARCHAR(150) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status VARCHAR(50) CHECK (status IN ('open', 'closed', 'locked')) NOT NULL DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_exercises_company FOREIGN KEY (company_id) REFERENCES companies(id) ON UPDATE CASCADE ON DELETE RESTRICT
);
CREATE INDEX IF NOT EXISTS idx_exercises_company_id ON exercises (company_id);
CREATE INDEX IF NOT EXISTS idx_exercises_status ON exercises (status);
CREATE INDEX IF NOT EXISTS idx_exercises_dates ON exercises (start_date, end_date);

CREATE TABLE IF NOT EXISTS accounts (
    id SERIAL PRIMARY KEY,
    company_id INTEGER NOT NULL,
    exercise_id INTEGER NULL,
    account_number VARCHAR(20) NOT NULL,
    name VARCHAR(150) NOT NULL,
    type VARCHAR(50) CHECK (type IN ('asset', 'liability', 'equity', 'expense', 'revenue')) NOT NULL,
    description TEXT NULL,
    is_active SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_accounts_company FOREIGN KEY (company_id) REFERENCES companies(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_accounts_exercise FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT unique_company_account UNIQUE ( company_id, account_number )
);
CREATE INDEX IF NOT EXISTS idx_accounts_company_id ON accounts (company_id);
CREATE INDEX IF NOT EXISTS idx_accounts_exercise_id ON accounts (exercise_id);
CREATE INDEX IF NOT EXISTS idx_accounts_type ON accounts (type);
CREATE INDEX IF NOT EXISTS idx_accounts_active ON accounts (is_active);

CREATE TABLE IF NOT EXISTS journals (
    id SERIAL PRIMARY KEY,
    company_id INTEGER NOT NULL,
    exercise_id INTEGER NULL,
    code VARCHAR(20) NOT NULL,
    name VARCHAR(150) NOT NULL,
    type VARCHAR(50) CHECK (type IN ('sales', 'purchases', 'cash', 'bank', 'miscellaneous')) NOT NULL,
    description TEXT NULL,
    is_active SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_journals_company FOREIGN KEY (company_id) REFERENCES companies(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_journals_exercise FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT unique_company_journal UNIQUE ( company_id, code )
);
CREATE INDEX IF NOT EXISTS idx_journals_company_id ON journals (company_id);
CREATE INDEX IF NOT EXISTS idx_journals_exercise_id ON journals (exercise_id);
CREATE INDEX IF NOT EXISTS idx_journals_type ON journals (type);
CREATE INDEX IF NOT EXISTS idx_journals_active ON journals (is_active);

CREATE TABLE IF NOT EXISTS entries (
    id SERIAL PRIMARY KEY,
    company_id INTEGER NOT NULL,
    exercise_id INTEGER NOT NULL,
    journal_id INTEGER NOT NULL,
    user_id INTEGER NULL,
    reference VARCHAR(50) NOT NULL,
    entry_date DATE NOT NULL,
    description TEXT NOT NULL,
    status VARCHAR(50) CHECK (status IN ('draft', 'validated', 'cancelled')) NOT NULL DEFAULT 'draft',
    validated_by INTEGER NULL,
    validated_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_entries_company FOREIGN KEY (company_id) REFERENCES companies(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_entries_exercise FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_entries_journal FOREIGN KEY (journal_id) REFERENCES journals(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_entries_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_entries_validator FOREIGN KEY (validated_by) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT unique_company_reference UNIQUE (company_id, reference)
);
CREATE INDEX IF NOT EXISTS idx_entries_exercise_id ON entries (exercise_id);
CREATE INDEX IF NOT EXISTS idx_entries_journal_id ON entries (journal_id);
CREATE INDEX IF NOT EXISTS idx_entries_user_id ON entries (user_id);
CREATE INDEX IF NOT EXISTS idx_entries_date ON entries (entry_date);
CREATE INDEX IF NOT EXISTS idx_entries_status ON entries (status);

CREATE TABLE IF NOT EXISTS entry_lines (
    id SERIAL PRIMARY KEY,
    entry_id INTEGER NOT NULL,
    account_id INTEGER NOT NULL,
    description VARCHAR(255) NULL,
    debit DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    credit DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_entry_lines_entry FOREIGN KEY (entry_id) REFERENCES entries(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_entry_lines_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON UPDATE CASCADE ON DELETE RESTRICT
);
CREATE INDEX IF NOT EXISTS idx_entry_lines_entry_id ON entry_lines (entry_id);
CREATE INDEX IF NOT EXISTS idx_entry_lines_account_id ON entry_lines (account_id);

CREATE TABLE IF NOT EXISTS taxes (
    id SERIAL PRIMARY KEY,
    company_id INTEGER NOT NULL,
    exercise_id INTEGER NOT NULL,
    name VARCHAR(150) NOT NULL,
    tax_type VARCHAR(100) NOT NULL,
    tax_period VARCHAR(100) NULL,
    tax_rate DECIMAL(8,4) NOT NULL DEFAULT 0.0000,
    taxable_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    tax_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    declaration_date DATE NULL,
    due_date DATE NULL,
    payment_date DATE NULL,
    status VARCHAR(50) CHECK (status IN ('pending', 'declared', 'paid', 'overdue')) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_taxes_company FOREIGN KEY (company_id) REFERENCES companies(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_taxes_exercise FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON UPDATE CASCADE ON DELETE RESTRICT
);
CREATE INDEX IF NOT EXISTS idx_taxes_company_id ON taxes (company_id);
CREATE INDEX IF NOT EXISTS idx_taxes_exercise_id ON taxes (exercise_id);
CREATE INDEX IF NOT EXISTS idx_taxes_type ON taxes (tax_type);
CREATE INDEX IF NOT EXISTS idx_taxes_status ON taxes (status);
CREATE INDEX IF NOT EXISTS idx_taxes_declaration_date ON taxes (declaration_date);
CREATE INDEX IF NOT EXISTS idx_taxes_due_date ON taxes (due_date);

CREATE TABLE IF NOT EXISTS documents (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    company_id INTEGER NOT NULL,
    exercise_id INTEGER NULL,
    name VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    file_size BIGINT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_documents_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_documents_company FOREIGN KEY (company_id) REFERENCES companies(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_documents_exercise FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON UPDATE CASCADE ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS idx_documents_user_id ON documents (user_id);
CREATE INDEX IF NOT EXISTS idx_documents_company_id ON documents (company_id);
CREATE INDEX IF NOT EXISTS idx_documents_exercise_id ON documents (exercise_id);
CREATE INDEX IF NOT EXISTS idx_documents_file_type ON documents (file_type);

CREATE TABLE IF NOT EXISTS notifications (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'info',
    status VARCHAR(50) CHECK (status IN ('unread', 'read')) NOT NULL DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_notifications_user_id ON notifications (user_id);
CREATE INDEX IF NOT EXISTS idx_notifications_status ON notifications (status);
CREATE INDEX IF NOT EXISTS idx_notifications_created_at ON notifications (created_at);

CREATE TABLE IF NOT EXISTS audit_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NULL,
    action VARCHAR(100) NOT NULL,
    entity VARCHAR(100) NULL,
    entity_id INTEGER NULL,
    description TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS idx_audit_logs_user_id ON audit_logs (user_id);
CREATE INDEX IF NOT EXISTS idx_audit_logs_action ON audit_logs (action);
CREATE INDEX IF NOT EXISTS idx_audit_logs_entity ON audit_logs (entity);
CREATE INDEX IF NOT EXISTS idx_audit_logs_created_at ON audit_logs (created_at);

CREATE TABLE IF NOT EXISTS invoices (
    id SERIAL PRIMARY KEY,
    company_id INTEGER NOT NULL,
    exercise_id INTEGER NULL,
    user_id INTEGER NULL,
    invoice_number VARCHAR(50) NOT NULL,
    description TEXT NULL,
    amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    status VARCHAR(50) CHECK (status IN ('draft', 'pending', 'paid', 'cancelled')) NOT NULL DEFAULT 'draft',
    invoice_date DATE NOT NULL,
    due_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_invoices_company FOREIGN KEY (company_id) REFERENCES companies(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_invoices_exercise FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_invoices_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT unique_company_invoice UNIQUE ( company_id, invoice_number )
);
CREATE INDEX IF NOT EXISTS idx_invoices_company_id ON invoices (company_id);
CREATE INDEX IF NOT EXISTS idx_invoices_exercise_id ON invoices (exercise_id);
CREATE INDEX IF NOT EXISTS idx_invoices_user_id ON invoices (user_id);
CREATE INDEX IF NOT EXISTS idx_invoices_status ON invoices (status);
CREATE INDEX IF NOT EXISTS idx_invoices_invoice_date ON invoices (invoice_date);
CREATE INDEX IF NOT EXISTS idx_invoices_due_date ON invoices (due_date);

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id BIGSERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_reset_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_password_reset_tokens_expiry ON password_reset_tokens (expires_at);
CREATE INDEX IF NOT EXISTS idx_password_reset_tokens_user ON password_reset_tokens (user_id);

-- 016_add_tax_period_and_due_date
ALTER TABLE taxes ADD COLUMN IF NOT EXISTS tax_period VARCHAR(100) NULL;
ALTER TABLE taxes ADD COLUMN IF NOT EXISTS due_date DATE NULL;
CREATE INDEX IF NOT EXISTS idx_taxes_due_date ON taxes (due_date);

