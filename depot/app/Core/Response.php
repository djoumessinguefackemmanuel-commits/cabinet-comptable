<?php

namespace App\Core;

class Response
{
    /**
     * Définir le code HTTP de la réponse.
     */
    public function setStatusCode(int $code): self
    {
        http_response_code($code);

        return $this;
    }

    /**
     * Définir un en-tête HTTP.
     */
    public function setHeader(string $name, string $value): self
    {
        header($name . ': ' . $value);

        return $this;
    }

    /**
     * Envoyer une réponse textuelle ou HTML.
     */
    public function send(string $content, int $statusCode = 200): void
    {
        http_response_code($statusCode);

        echo $content;
    }

    /**
     * Envoyer une réponse JSON.
     */
    public function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);

        header('Content-Type: application/json; charset=UTF-8');

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Rediriger vers une URL.
     */
    public function redirect(string $url, int $statusCode = 302): void
    {
        http_response_code($statusCode);

        header('Location: ' . $url);

        exit;
    }

    /**
     * Envoyer une réponse indiquant une ressource introuvable.
     */
    public function notFound(string $message = 'Page introuvable.'): void
    {
        $this->send($message, 404);
    }

    /**
     * Envoyer une réponse indiquant un accès interdit.
     */
    public function forbidden(string $message = 'Accès interdit.'): void
    {
        $this->send($message, 403);
    }

    /**
     * Envoyer une réponse indiquant une erreur serveur.
     */
    public function serverError(
        string $message = 'Une erreur interne est survenue.'
    ): void {
        $this->send($message, 500);
    }
}
