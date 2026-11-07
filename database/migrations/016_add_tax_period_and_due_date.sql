ALTER TABLE taxes
    ADD COLUMN tax_period VARCHAR(100) NULL AFTER tax_type,
    ADD COLUMN due_date DATE NULL AFTER declaration_date,
    ADD INDEX idx_taxes_due_date (due_date);
