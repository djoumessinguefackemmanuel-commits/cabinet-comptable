<?php

declare(strict_types=1);

// Point d'entrée Vercel (runtime PHP natif) : toutes les requêtes non statiques arrivent ici.
// Les erreurs sont envoyées dans les journaux Vercel (jamais affichées aux visiteurs).
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', 'php://stderr');

register_shutdown_function(static function (): void {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log('FATAL ' . $error['message'] . ' @ ' . $error['file'] . ':' . $error['line']);
    }
});

try {
    require dirname(__DIR__) . '/bootstrap.php';
} catch (Throwable $exception) {
    error_log('EXCEPTION ' . get_class($exception) . ': ' . $exception->getMessage() . ' @ ' . $exception->getFile() . ':' . $exception->getLine());
    http_response_code(500);
    echo 'Erreur interne du serveur.';
}
