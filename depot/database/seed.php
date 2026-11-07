<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/constants.php';
require_once BASE_PATH . '/config/database.php';

spl_autoload_register(function (string $class): void {
    $prefixes = [
        'App\\' => BASE_PATH . '/app/',
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

try {
    $pdo = App\Core\Database::connect();

    (new Database\Seeders\RolesSeeder())->run($pdo);
    (new Database\Seeders\AdminSeeder())->run($pdo);

    fwrite(STDOUT, "Les rôles et le compte administrateur sont initialisés.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, "Échec de l'initialisation : {$exception->getMessage()}\n");
    exit(1);
}
