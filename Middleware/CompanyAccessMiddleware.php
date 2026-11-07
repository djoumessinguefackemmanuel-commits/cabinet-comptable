<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Models\UserCompany;

class CompanyAccessMiddleware
{
    /**
     * Vérifier l'accès de l'utilisateur à un cabinet.
     */
    public function handle(int $companyId): bool
    {
        if (!Auth::check()) {

            header('Location: ' . url('login'));

            exit;
        }

        $user = Auth::user();

        // L'administrateur possède un accès global.
        if (($user['role'] ?? null) === ROLE_ADMIN) {
            return true;
        }

        $userCompanyModel = new UserCompany();

        if (!$userCompanyModel->hasAccess(Auth::id(), $companyId)) {

            http_response_code(403);

            $errorView = VIEW_PATH . '/errors/403.php';

            if (file_exists($errorView)) {
                require $errorView;
            } else {
                echo 'Erreur 403 : Accès à ce cabinet refusé.';
            }

            exit;
        }

        return true;
    }
}
