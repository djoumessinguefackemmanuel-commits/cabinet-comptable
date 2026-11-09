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

        // Export CSV / Excel / PDF du tableau affiché (?export=csv|xlsx|pdf).
        if (
            ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET'
            && Auth::check()
            && \App\Services\TableExporter::supports($_GET['export'] ?? null)
        ) {
            $this->exportContent($content, (string) $_GET['export'], (string) ($data['title'] ?? 'export'));
        }

        require $layoutFile;
    }

    /**
     * Exporter le contenu de la page puis terminer la requête.
     */
    private function exportContent(string $content, string $format, string $title): void
    {
        $user = Auth::user() ?? [];
        $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));

        try {
            \App\Models\AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'export',
                'entity' => 'export_' . $format,
                'description' => mb_substr(
                    'Export ' . strtoupper($format) . ' : ' . (string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH),
                    0,
                    500
                ),
                'ip_address' => $_SERVER['HTTP_X_REAL_IP'] ?? ($_SERVER['REMOTE_ADDR'] ?? null),
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? mb_substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : null,
            ]);
        } catch (\Throwable $exception) {
            error_log('Journal d\'audit (export) : ' . $exception->getMessage());
        }

        \App\Services\TableExporter::send($content, $format, $title, [
            'subtitle' => trim(APP_NAME . ($name !== '' ? ' | ' . $name : '')),
        ]);
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
