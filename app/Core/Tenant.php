<?php

namespace App\Core;

use PDO;

/**
 * Isolation des données entre cabinets clients (multi-tenant).
 *
 * - Un cabinet (tenants) regroupe ses entreprises et ses utilisateurs.
 * - L'administrateur SANS cabinet (tenant_id NULL) est le propriétaire de la plateforme : il voit tout.
 * - Tout autre utilisateur ne voit que les données de son cabinet.
 */
class Tenant
{
    private static ?array $companyIds = null;

    /** Tables rattachées à un cabinet via company_id. */
    private const BY_COMPANY = ['exercises', 'journals', 'accounts', 'entries', 'documents', 'invoices', 'taxes', 'bank_statement_lines', 'fixed_assets'];

    /** Paramètres de requête (GET/POST) qui désignent des enregistrements à contrôler. */
    private const PARAM_TABLES = [
        'company_id' => 'companies', 'company_ids' => 'companies',
        'exercise_id' => 'exercises', 'journal_id' => 'journals', 'account_id' => 'accounts',
        'entry_id' => 'entries', 'user_id' => 'users', 'client_id' => 'users',
        'document_id' => 'documents', 'asset_id' => 'fixed_assets', 'invoice_id' => 'invoices',
    ];

    /** Crée les colonnes/tables nécessaires si la migration n'a pas encore été appliquée. */
    public static function ensureSchema(): void
    {
        if (!empty($_SESSION['_tenant_schema_ok'])) {
            return;
        }

        $pdo = Database::connect();
        $pg = defined('DB_DRIVER') && DB_DRIVER === 'pgsql';
        $id = $pg ? 'SERIAL PRIMARY KEY' : 'INT AUTO_INCREMENT PRIMARY KEY';
        $statements = [
            "CREATE TABLE IF NOT EXISTS tenants (id $id, name VARCHAR(150) NOT NULL, status VARCHAR(20) NOT NULL DEFAULT 'active', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
        ];

        try {
            $pdo->query('SELECT tenant_id FROM users LIMIT 0');
            $pdo->query('SELECT tenant_id FROM companies LIMIT 0');
        } catch (\Throwable $e) {
            if ($pg && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $statements[] = $pg
                ? 'ALTER TABLE users ADD COLUMN IF NOT EXISTS tenant_id INTEGER NULL'
                : 'ALTER TABLE users ADD COLUMN tenant_id INT NULL';
            $statements[] = $pg
                ? 'ALTER TABLE companies ADD COLUMN IF NOT EXISTS tenant_id INTEGER NULL'
                : 'ALTER TABLE companies ADD COLUMN tenant_id INT NULL';
            $statements[] = "INSERT INTO tenants (name) SELECT 'Cabinet principal' WHERE NOT EXISTS (SELECT 1 FROM tenants)";
            $statements[] = 'UPDATE companies SET tenant_id = (SELECT MIN(id) FROM tenants) WHERE tenant_id IS NULL';
            $statements[] = "UPDATE users SET tenant_id = (SELECT MIN(id) FROM tenants) WHERE tenant_id IS NULL AND id <> (SELECT MIN(x.id) FROM (SELECT u.id FROM users u JOIN roles r ON r.id = u.role_id WHERE r.name = 'admin') x)";
        }

        foreach ($statements as $sql) {
            try {
                $pdo->exec($sql);
            } catch (\Throwable $e) {
                error_log('Tenant::ensureSchema : ' . $e->getMessage());
            }
        }

        $_SESSION['_tenant_schema_ok'] = true;
    }

    /** Cabinet de l'utilisateur connecté (null = propriétaire de la plateforme ou invité). */
    public static function id(): ?int
    {
        $tenantId = $_SESSION['user']['tenant_id'] ?? null;

        return $tenantId === null || $tenantId === '' ? null : (int) $tenantId;
    }

    /** Propriétaire de la plateforme : administrateur sans cabinet. */
    public static function isOwner(): bool
    {
        return Auth::check()
            && ($_SESSION['user']['role'] ?? '') === ROLE_ADMIN
            && self::id() === null;
    }

    /** Identifiants des entreprises du cabinet courant ; null pour le propriétaire (aucune limite). */
    public static function companyIds(): ?array
    {
        if (self::isOwner()) {
            return null;
        }

        if (self::$companyIds === null) {
            $tenantId = self::id();
            if ($tenantId === null) {
                self::$companyIds = [];
            } else {
                $st = Database::connect()->prepare('SELECT id FROM companies WHERE tenant_id = ?');
                $st->execute([$tenantId]);
                self::$companyIds = array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));
            }
        }

        return self::$companyIds;
    }

    /** Liste des cabinets (propriétaire uniquement). */
    public static function all(): array
    {
        if (!self::isOwner()) {
            return [];
        }

        return Database::connect()->query(
            "SELECT t.*,
                    (SELECT COUNT(*) FROM companies c WHERE c.tenant_id = t.id) AS companies_count,
                    (SELECT COUNT(*) FROM users u WHERE u.tenant_id = t.id) AS users_count
             FROM tenants t ORDER BY t.id ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cabinet à attribuer à un nouvel enregistrement : celui de l'utilisateur ;
     * le propriétaire le choisit dans le formulaire (champ tenant_id).
     *
     * @return int|null|false false si le choix du propriétaire est invalide
     */
    public static function forNewRecord(): int|null|false
    {
        if (!self::isOwner()) {
            return self::id() ?? false;
        }

        $chosen = (int) ($_POST['tenant_id'] ?? 0);
        $st = Database::connect()->prepare('SELECT id FROM tenants WHERE id = ?');
        $st->execute([$chosen]);

        return $st->fetchColumn() ? $chosen : false;
    }

    public static function reset(): void
    {
        self::$companyIds = null;
    }

    /** Fragment SQL « cabinet de l'utilisateur » sur une colonne tenant_id : [sql, paramètres]. */
    public static function where(string $column): array
    {
        if (self::isOwner()) {
            return ['1 = 1', []];
        }

        $tenantId = self::id();

        return $tenantId === null ? ['1 = 0', []] : ["$column = ?", [$tenantId]];
    }

    /** L'enregistrement $id de la table $table appartient-il au cabinet courant ? (false si inexistant) */
    public static function owns(string $table, int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $pdo = Database::connect();
        $owner = self::isOwner();
        $tenantId = self::id();

        if (!$owner && $tenantId === null) {
            return false;
        }

        if ($table === 'bank_statement_lines') {
            \App\Models\BankStatement::ensureSchema();
        }
        if ($table === 'fixed_assets') {
            \App\Models\FixedAsset::ensureSchema();
        }

        $sql = match (true) {
            $table === 'companies' => 'SELECT tenant_id FROM companies WHERE id = ?',
            $table === 'users' => 'SELECT tenant_id FROM users WHERE id = ?',
            in_array($table, self::BY_COMPANY, true) => "SELECT c.tenant_id FROM $table t INNER JOIN companies c ON c.id = t.company_id WHERE t.id = ?",
            $table === 'support_requests' => 'SELECT u.tenant_id FROM support_requests s LEFT JOIN users u ON u.id = s.user_id WHERE s.id = ?',
            $table === 'audit_logs' => 'SELECT u.tenant_id FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id WHERE a.id = ?',
            default => throw new \InvalidArgumentException('Table non gérée : ' . $table),
        };

        $st = $pdo->prepare($sql);
        $st->execute([$id]);
        $row = $st->fetch(PDO::FETCH_NUM);

        if ($row === false) {
            return false;
        }

        if ($owner) {
            return true;
        }

        return $row[0] !== null && (int) $row[0] === $tenantId;
    }

    /** Vérifie les identifiants présents dans l'URL, le GET et le POST. */
    public static function requestAllowed(string $path): bool
    {
        if (self::isOwner()) {
            return true;
        }

        $routes = [
            '#^/users/(\d+)(?:/|$)#' => 'users',
            '#^/account/avatar/(\d+)$#' => 'users',
            '#^/companies/(\d+)(?:/|$)#' => 'companies',
            '#^/exercises/(\d+)(?:/|$)#' => 'exercises',
            '#^/(?:accounting|api)/entries/(\d+)(?:/|$)#' => 'entries',
            '#^/accounting/reconciliation/(\d+)(?:/|$)#' => 'bank_statement_lines',
            '#^/fixed-assets/(\d+)(?:/|$)#' => 'fixed_assets',
            '#^/documents/(\d+)(?:/|$)#' => 'documents',
            '#^/invoices/(\d+)(?:/|$)#' => 'invoices',
            '#^/admin/support/(\d+)(?:/|$)#' => 'support_requests',
            '#^/audit/(\d+)(?:/|$)#' => 'audit_logs',
        ];

        foreach ($routes as $pattern => $table) {
            if (preg_match($pattern, $path, $m) === 1) {
                if ($table === 'users' && str_starts_with($path, '/account/avatar/') && (int) $m[1] === (int) Auth::id()) {
                    continue;
                }
                if (!self::owns($table, (int) $m[1])) {
                    return false;
                }
            }
        }

        return self::paramsAllowed($_GET) && self::paramsAllowed($_POST);
    }

    private static function paramsAllowed(array $data): bool
    {
        foreach ($data as $key => $value) {
            if (is_string($key) && isset(self::PARAM_TABLES[$key])) {
                foreach ((array) $value as $one) {
                    if (is_array($one)) {
                        continue;
                    }
                    $n = (int) $one;
                    if ($n > 0 && !self::owns(self::PARAM_TABLES[$key], $n)) {
                        return false;
                    }
                }
            }

            if (is_array($value) && !self::paramsAllowed($value)) {
                return false;
            }
        }

        return true;
    }
}
