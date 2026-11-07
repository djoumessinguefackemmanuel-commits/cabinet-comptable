<?php

namespace App\Middleware;

use App\Core\Auth;

class AuthMiddleware
{
    /**
     * Vérifier que l'utilisateur est connecté.
     */
    public function handle(): bool
    {
        if (!Auth::check()) {

            header('Location: ' . url('login'));

            exit;
        }

        return true;
    }
}
