<?php

namespace App\Core;

class Request
{
    /**
     * Récupérer la méthode HTTP.
     */
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Récupérer l'URI de la requête.
     */
    public function uri(): string
    {
        return parse_url(
            $_SERVER['REQUEST_URI'] ?? '/',
            PHP_URL_PATH
        );
    }

    /**
     * Vérifier si la requête est GET.
     */
    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    /**
     * Vérifier si la requête est POST.
     */
    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    /**
     * Récupérer une valeur GET.
     */
    public function get(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Récupérer une valeur POST.
     */
    public function post(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Récupérer toutes les données GET.
     */
    public function allGet(): array
    {
        return $_GET;
    }

    /**
     * Récupérer toutes les données POST.
     */
    public function allPost(): array
    {
        return $_POST;
    }

    /**
     * Récupérer toutes les données de la requête.
     */
    public function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    /**
     * Récupérer les données JSON.
     */
    public function json(): array
    {
        $content = file_get_contents('php://input');

        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    /**
     * Vérifier si la requête est AJAX.
     */
    public function isAjax(): bool
    {
        return strtolower(
            $_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''
        ) === 'xmlhttprequest';
    }
}
