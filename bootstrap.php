<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Point d'entrée principal de l'application
|--------------------------------------------------------------------------
*/

// Définir le chemin racine du projet.
define('BASE_PATH', __DIR__);

// Charger les configurations.
require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/constants.php';
require_once BASE_PATH . '/config/permissions.php';

if (is_file(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// Charger la configuration de la base de données.
require_once BASE_PATH . '/config/database.php';

if (APP_ENV === 'production') {
    $baseUrl = parse_url(BASE_URL);
    $forwardedProto = strtolower(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')[0]);
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || $forwardedProto === 'https';

    if (!$isHttps) {
        $host = $baseUrl['host'] ?? '';
        $port = isset($baseUrl['port']) ? ':' . $baseUrl['port'] : '';
        if ($host === '') {
            http_response_code(500);
            exit('Configuration HTTPS invalide.');
        }

        header(
            'Location: https://' . $host . $port . ($_SERVER['REQUEST_URI'] ?? '/'),
            true,
            308
        );
        exit;
    }

    header('Strict-Transport-Security: max-age=31536000');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// Autoloader simple pour les classes de l'application.
spl_autoload_register(function (string $class): void {
    $prefixes = [
        'App\\'              => BASE_PATH . '/app/',
        'Database\\Seeders\\' => BASE_PATH . '/database/seeders/'
    ];

    foreach ($prefixes as $prefix => $directory) {
        if (strpos($class, $prefix) !== 0) {
            continue;
        }

        $relativeClass = substr($class, strlen($prefix));

        $file = $directory
            . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass)
            . '.php';

        if (is_file($file)) {
            require_once $file;
        }

        return;
    }
});

// Charger les fonctions d'aide.
require_once BASE_PATH . '/app/Core/Helpers.php';

// Démarrer la session avec le bon nom et les bons paramètres.
use App\Core\Session;
Session::start();

// Créer l'application.
use App\Core\Application;

$app = new Application();

// Récupérer le routeur.
$router = $app->getRouter();

// Charger les routes web.
require_once BASE_PATH . '/routes/web.php';

// Charger les routes API.
require_once BASE_PATH . '/routes/api.php';

// Exécuter l'application.
$app->run();
