<?php
namespace App\Core;

class Application
{
    /**
     * Instance du routeur.
     */
    private Router $router;

    /**
     * Constructeur de l'application.
     */
    public function __construct()
    {
        $this->router = new Router();
    }

    /**
     * Retourner l'instance du routeur.
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Charger les routes de l'application.
     */
    private function loadRoutes(): void
    {
        $router = $this->router;

        $routesFile = ROOT_PATH . '/routes/web.php';

        if (!file_exists($routesFile)) {
            throw new \RuntimeException(
                'Le fichier des routes web est introuvable.'
            );
        }

        require $routesFile;
    }

    /**
     * Démarrer l'application.
     */
    public function run(): void
    {
        $this->router->dispatch();
    }
}
