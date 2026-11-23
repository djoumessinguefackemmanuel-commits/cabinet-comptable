<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord selon le rôle.
     */
    public function index(): void
    {
        $user = Auth::user();

        if (!$user) {
            $this->redirect(url('login'));
            return;
        }

        switch ($user['role'] ?? '') {

            case ROLE_ADMIN:
                $this->admin();
                break;

            case ROLE_EXPERT:
                $this->expert();
                break;

            case ROLE_ACCOUNTANT:
                $this->accountant();
                break;

            case ROLE_CLIENT:
                $this->client();
                break;

            default:
                Auth::logout();
                $this->redirect(url('login'));
                break;
        }
    }

    /**
     * Tableau de bord de l'administrateur.
     */
    public function admin(): void
    {
        $pdo = Database::getConnection();

        $data = [
            'stats' => [
                'total_users' => $this->countRows($pdo, 'users'),
                'active_users' => $this->countActiveUsers($pdo),
                'total_companies' => $this->countRows($pdo, 'companies'),
                'total_exercises' => $this->countRows($pdo, 'exercises'),
                'total_entries' => $this->countRows($pdo, 'entries')
            ],
            'recentUsers' => $this->scopedRows($pdo, "
                SELECT u.first_name, u.last_name, u.email, u.status, r.name AS role_name
                FROM users u
                LEFT JOIN roles r ON r.id = u.role_id
                WHERE {scope}
                ORDER BY u.created_at DESC
                LIMIT 5
            ", 'u.tenant_id'),
            'recentCompanies' => $this->scopedRows($pdo, "
                SELECT c.name, c.email, c.phone, c.city
                FROM companies c
                WHERE {scope}
                ORDER BY c.created_at DESC
                LIMIT 5
            ", 'c.tenant_id'),
            'recentActivities' => $this->scopedRows($pdo, "
                SELECT l.action, l.description, l.created_at
                FROM audit_logs l
                LEFT JOIN users lu ON lu.id = l.user_id
                WHERE {scope}
                ORDER BY l.created_at DESC
                LIMIT 5
            ", 'lu.tenant_id')
        ];

        $this->render('admin.dashboard', $data, 'layouts.main');
    }

    /**
     * Tableau de bord de l'expert-comptable.
     */
    public function expert(): void
    {
        $pdo = Database::getConnection();

        $data = [
            'totalCompanies' => $this->countRows($pdo, 'companies'),
            'totalExercises' => $this->countRows($pdo, 'exercises'),
            'totalEntries' => $this->countRows($pdo, 'entries'),
            'totalPendingEntries' => $this->countEntriesByStatus($pdo, ENTRY_DRAFT)
        ];

        $this->render('expert.dashboard', $data);
    }

    /**
     * Tableau de bord du comptable.
     */
    public function accountant(): void
    {
        $pdo = Database::getConnection();
        $companyIds = Auth::companyIds() ?? [];

        $data = [
            'totalCompanies' => count($companyIds),
            'totalExercises' => $this->countRowsByCompanies($pdo, 'exercises', $companyIds),
            'totalEntries' => $this->countRowsByCompanies($pdo, 'entries', $companyIds),
            'totalJournals' => $this->countRowsByCompanies($pdo, 'journals', $companyIds),
            'recentEntries' => $this->recentEntries($pdo, $companyIds)
        ];

        $this->render('accountant.dashboard', $data);
    }

    /**
     * Tableau de bord du client.
     */
    public function client(): void
    {
        $pdo = Database::getConnection();

        $userId = Auth::id();

        $companies = $pdo->prepare("
            SELECT COUNT(*)
            FROM user_companies
            WHERE user_id = :user_id
        ");
        $companies->execute(['user_id' => $userId]);

        $companyIds = $pdo->prepare("
            SELECT company_id
            FROM user_companies
            WHERE user_id = :user_id
        ");
        $companyIds->execute(['user_id' => $userId]);
        $companyIds = $companyIds->fetchAll(\PDO::FETCH_COLUMN);

        $invoiceStats = ['total' => 0, 'pending' => 0];
        $recentInvoices = [];
        if ($companyIds !== []) {
            $placeholders = implode(',', array_fill(0, count($companyIds), '?'));
            $stats = $pdo->prepare("
                SELECT COUNT(*) AS total,
                       SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending
                FROM invoices
                WHERE company_id IN ({$placeholders})
            ");
            $stats->execute($companyIds);
            $invoiceStats = $stats->fetch() ?: $invoiceStats;

            $invoices = $pdo->prepare("
                SELECT invoice_number, description, invoice_date, amount, status
                FROM invoices
                WHERE company_id IN ({$placeholders})
                ORDER BY invoice_date DESC, id DESC
                LIMIT 5
            ");
            $invoices->execute($companyIds);
            $recentInvoices = $invoices->fetchAll();
        }

        $data = [
            'totalInvoices' => (int) $invoiceStats['total'],
            'pendingInvoices' => (int) $invoiceStats['pending'],
            'totalExercises' => $this->countRowsByCompanies(
                $pdo,
                'exercises',
                array_map('intval', $companyIds)
            ),
            'recentInvoices' => $recentInvoices
        ];

        $this->render('client.dashboard', $data);
    }

    /**
     * Compter les enregistrements d'une table.
     */
    /** Exécute une requête où {scope} est remplacé par le filtre « cabinet de l'utilisateur ». */
    private function scopedRows($pdo, string $sql, string $tenantColumn): array
    {
        [$scope, $params] = \App\Core\Tenant::where($tenantColumn);
        $statement = $pdo->prepare(str_replace('{scope}', $scope, $sql));
        $statement->execute($params);

        return $statement->fetchAll();
    }

    private function countRows($pdo, string $table): int
    {
        $allowedTables = [
            'users',
            'companies',
            'exercises',
            'entries'
        ];

        if (!in_array($table, $allowedTables, true)) {
            return 0;
        }

        // Comptages limités au cabinet de l'utilisateur (le propriétaire de la plateforme voit tout).
        if ($table === 'users') {
            return \App\Models\User::countAll();
        }
        if ($table === 'companies') {
            return \App\Models\Company::countAll();
        }

        $companyIds = Auth::companyIds();
        if ($companyIds !== null) {
            return $this->countRowsByCompanies($pdo, $table, $companyIds);
        }

        $stmt = $pdo->query("SELECT COUNT(*) FROM {$table}");

        return (int) $stmt->fetchColumn();
    }

    private function countRowsByCompanies($pdo, string $table, array $companyIds): int
    {
        if ($companyIds === []) {
            return 0;
        }

        if (!in_array($table, ['exercises', 'entries', 'journals'], true)) {
            throw new \InvalidArgumentException('Table non autorisée pour le comptage par entreprise.');
        }

        $placeholders = implode(',', array_fill(0, count($companyIds), '?'));
        $statement = $pdo->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE company_id IN ({$placeholders})"
        );
        $statement->execute($companyIds);

        return (int) $statement->fetchColumn();
    }

    private function recentEntries($pdo, array $companyIds): array
    {
        if ($companyIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($companyIds), '?'));
        $statement = $pdo->prepare("
            SELECT e.reference, e.entry_date, e.description, e.status,
                   COALESCE(SUM(el.debit), 0) AS amount
            FROM entries e
            LEFT JOIN entry_lines el ON el.entry_id = e.id
            WHERE e.company_id IN ({$placeholders})
            GROUP BY e.id
            ORDER BY e.entry_date DESC, e.id DESC
            LIMIT 5
        ");
        $statement->execute($companyIds);

        return $statement->fetchAll();
    }

    /**
     * Compter les écritures selon leur statut.
     */
    private function countEntriesByStatus($pdo, string $status): int
    {
        $companyIds = Auth::companyIds();
        if ($companyIds === []) {
            return 0;
        }

        $filter = $companyIds === null
            ? ''
            : ' AND company_id IN (' . implode(',', array_map('intval', $companyIds)) . ')';
        $stmt = $pdo->prepare(
            "SELECT COUNT(*)
             FROM entries
             WHERE status = :status{$filter}"
        );

        $stmt->execute([
            'status' => $status
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Compter les comptes utilisateurs actifs.
     */
    private function countActiveUsers($pdo): int
    {
        return \App\Models\User::countByStatus(USER_ACTIVE);
    }
}