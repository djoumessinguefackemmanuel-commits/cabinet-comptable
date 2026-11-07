<?php

namespace App\Middleware;

use App\Core\Auth;

class RoleMiddleware
{
    /**
     * Vérifier si l'utilisateur possède l'un des rôles autorisés.
     */
    public function handle(array $roles): bool
    {
        if (!Auth::check()) {

            header('Location: ' . url('login'));

            exit;
        }

        $user = Auth::user();

        $userRole = $user['role'] ?? null;

        if (!in_array($userRole, $roles, true)) {

            http_response_code(403);

            $errorView = VIEW_PATH . '/errors/403.php';

            if (file_exists($errorView)) {
                require $errorView;
            } else {
                echo 'Erreur 403 : Vous n\'êtes pas autorisé à accéder à cette page.';
            }

            exit;
        }

        return true;
    }
}
