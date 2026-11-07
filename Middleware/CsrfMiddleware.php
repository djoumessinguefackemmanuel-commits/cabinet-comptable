<?php

namespace App\Middleware;

class CsrfMiddleware
{
    /**
     * Vérifier le jeton CSRF des requêtes qui modifient les données.
     */
    public function handle(): bool
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Les requêtes GET, HEAD et OPTIONS ne nécessitent pas de vérification.
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return true;
        }

        $sessionToken = $_SESSION['_csrf_token'] ?? '';

        $requestToken = $_POST['_token'] ?? '';

        if (
            empty($sessionToken)
            || empty($requestToken)
            || !hash_equals($sessionToken, $requestToken)
        ) {

            http_response_code(419);

            echo 'Erreur 419 : La session a expiré ou le jeton de sécurité est invalide.';

            exit;
        }

        return true;
    }
}
