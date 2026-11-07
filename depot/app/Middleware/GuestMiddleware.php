<?php

namespace App\Middleware;

use App\Core\Auth;

class GuestMiddleware
{
    /**
     * Vérifier que l'utilisateur n'est pas déjà connecté.
     */
    public function handle(): bool
    {
        if (Auth::check()) {

            $user = Auth::user();

            $role = $user['role'] ?? null;

            switch ($role) {

                case ROLE_ADMIN:
                    header('Location: ' . url('admin/dashboard'));
                    break;

                case ROLE_EXPERT:
                    header('Location: ' . url('expert/dashboard'));
                    break;

                case ROLE_ACCOUNTANT:
                    header('Location: ' . url('accountant/dashboard'));
                    break;

                case ROLE_CLIENT:
                    header('Location: ' . url('client/dashboard'));
                    break;

                default:
                    header('Location: ' . url('dashboard'));
                    break;
            }

            exit;
        }

        return true;
    }
}
