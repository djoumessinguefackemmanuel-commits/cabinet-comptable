<?php
namespace App\Core;

class Controller
{
    /**
     * Charger une vue.
     */
    protected function view(string $view, array $data = []): void
    {
        $this->render($view, $data);
    }

    /**
     * Charger une vue avec un layout.
     */
    protected function render(
        string $view,
        array $data = [],
        string $layout = 'layouts.main'
    ): void {
        $viewFile = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';

        $layoutFile = VIEW_PATH . '/'
                    . str_replace('.', '/', $layout)
                    . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException(
                "La vue '{$view}' est introuvable."
            );
        }

        if (!file_exists($layoutFile)) {
            throw new \RuntimeException(
                "Le layout '{$layout}' est introuvable."
            );
        }

        extract($data, EXTR_SKIP);

        ob_start();

        require $viewFile;

        $content = ob_get_clean();

        require $layoutFile;
    }

    /**
     * Rediriger vers une URL.
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Retourner une réponse JSON.
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);

        header('Content-Type: application/json; charset=UTF-8');

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        exit;
    }

    /**
     * Vérifier l'existence d'une vue.
     */
    protected function viewExists(string $view): bool
    {
        $viewFile = VIEW_PATH . '/'
                  . str_replace('.', '/', $view)
                  . '.php';

        return file_exists($viewFile);
    }

    /**
     * Afficher une erreur HTTP sans dépendre d'une vue métier.
     */
    protected function renderError(int $statusCode): void
    {
        http_response_code($statusCode);
        $errorView = VIEW_PATH . '/errors/' . $statusCode . '.php';

        if (is_file($errorView)) {
            require $errorView;
            return;
        }

        echo $statusCode === 404
            ? 'Erreur 404 : Page introuvable.'
            : 'Une erreur est survenue.';
    }
}
