<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Core\Tenant;
use App\Models\Exercise;
use App\Models\User;
use App\Models\UserCompany;

class RouteAccessMiddleware
{
    private const PUBLIC_ROUTES = [
        'GET /',
        'GET /login',
        'POST /login',
        'GET /login/2fa',
        'POST /login/2fa',
        'GET /forgot-password',
        'POST /forgot-password',
        'GET /reset-password',
        'POST /reset-password',
        'GET /contact-admin',
        'POST /contact-admin',
        'POST /api/login'
    ];

    public function handle(string $method, string $path): bool
    {
        if (in_array($method . ' ' . $path, self::PUBLIC_ROUTES, true)) {
            return true;
        }

        if (!Auth::check()) {
            header('Location: ' . url('login'));
            exit;
        }

        Tenant::ensureSchema();

        $user = User::findById((int) Auth::id());
        if ($user) {
            // Le cabinet est relu à chaque requête : un changement s'applique immédiatement.
            $_SESSION['user']['tenant_id'] = $user['tenant_id'] ?? null;
            $_SESSION['user']['role'] = $user['role'] ?? ($_SESSION['user']['role'] ?? null);
            Tenant::reset();
        }

        if ($user && ($user['tenant_id'] ?? null) !== null) {
            $tenantStatus = Database::connect()->prepare('SELECT status FROM tenants WHERE id = ?');
            $tenantStatus->execute([(int) $user['tenant_id']]);
            if (($tenantStatus->fetchColumn() ?: 'suspended') !== 'active') {
                Auth::logout();
                Session::set('error', 'L\'accès de votre cabinet est suspendu. Contactez le support.');
                header('Location: ' . url('login'));
                exit;
            }
        }

        if (!$user || ($user['status'] ?? '') !== USER_ACTIVE) {
            Auth::logout();
            Session::set('error', 'Votre session a expiré ou votre compte n’est plus actif.');
            header('Location: ' . url('login'));
            exit;
        }

        // Première connexion avec un mot de passe temporaire : seul le choix du mot de passe est permis.
        if ((int) ($user['must_change_password'] ?? 0) === 1) {
            $allowed = ['/account/welcome', '/logout'];
            if (!in_array($path, $allowed, true)) {
                header('Location: ' . url('account/welcome'));
                exit;
            }

            return true;
        }

        $role = $user['role'] ?? '';
        if (!$this->roleCanAccess($method, $path, $role)) {
            $this->deny($path);
            return false;
        }

        if (str_starts_with($path, '/platform') && !Tenant::isOwner()) {
            $this->deny($path);
            return false;
        }

        // Isolation entre cabinets : aucun identifiant d'un autre cabinet n'est accepté.
        if (!Tenant::requestAllowed($path)) {
            $this->deny($path);
            return false;
        }

        if (!$this->companyScopeAllows($method, $path, $role)) {
            $this->deny($path);
            return false;
        }

        return true;
    }

    private function roleCanAccess(string $method, string $path, string $role): bool
    {
        if ($role === ROLE_ADMIN) {
            return true;
        }

        $policies = require CONFIG_PATH . '/route_access.php';
        foreach ($policies as $policy) {
            if (
                in_array($method, $policy['methods'], true) &&
                preg_match($policy['pattern'], $path) === 1
            ) {
                return in_array($role, $policy['roles'], true);
            }
        }

        return false;
    }

    private function companyScopeAllows(string $method, string $path, string $role): bool
    {
        if (in_array($role, [ROLE_ADMIN, ROLE_EXPERT], true)) {
            return true;
        }

        $userId = Auth::id();
        if ($userId === null) {
            return false;
        }

        $companyIds = UserCompany::getCompanyIdsByUser($userId);
        $requestedCompanyId = (int) ($_GET['company_id'] ?? $_POST['company_id'] ?? 0);

        $exerciseId = (int) ($_GET['exercise_id'] ?? $_POST['exercise_id'] ?? 0);
        if ($exerciseId > 0) {
            $exercise = Exercise::findById($exerciseId);
            if (!$exercise || !in_array((int) $exercise['company_id'], $companyIds, true)) {
                return false;
            }
            $requestedCompanyId = (int) $exercise['company_id'];
        }

        if (preg_match('#^/invoices/(\d+)(?:/|$)#', $path, $matches)) {
            $statement = Database::connect()->prepare(
                'SELECT company_id FROM invoices WHERE id = :id'
            );
            $statement->execute(['id' => (int) $matches[1]]);
            $recordCompanyId = $statement->fetchColumn();
            if ($recordCompanyId === false) {
                return false;
            }
            $requestedCompanyId = (int) $recordCompanyId;
        } elseif (preg_match('#^/(?:accounting|api)/entries/(\d+)(?:/|$)#', $path, $matches)) {
            $statement = Database::connect()->prepare(
                'SELECT company_id FROM entries WHERE id = :id'
            );
            $statement->execute(['id' => (int) $matches[1]]);
            $recordCompanyId = $statement->fetchColumn();
            if ($recordCompanyId === false) {
                return false;
            }
            $requestedCompanyId = (int) $recordCompanyId;
        } elseif (preg_match('#^/documents/(\d+)/(?:download|delete)$#', $path, $matches)) {
            $statement = Database::connect()->prepare(
                'SELECT company_id FROM documents WHERE id = :id'
            );
            $statement->execute(['id' => (int) $matches[1]]);
            $recordCompanyId = $statement->fetchColumn();
            if ($recordCompanyId === false) {
                return false;
            }
            $requestedCompanyId = (int) $recordCompanyId;
        }

        if ($requestedCompanyId > 0) {
            return in_array($requestedCompanyId, $companyIds, true);
        }

        return true;
    }

    private function deny(string $path): void
    {
        http_response_code(403);

        if (str_starts_with($path, '/api/')) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(
                ['error' => 'Accès interdit.'],
                JSON_UNESCAPED_UNICODE
            );
            return;
        }

        $errorView = VIEW_PATH . '/errors/403.php';
        if (is_file($errorView)) {
            require $errorView;
            return;
        }

        echo 'Erreur 403 : Accès interdit.';
    }
}
