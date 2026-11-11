<?php

require_once __DIR__ . '/env.php';

/**
 * ==========================================================
 * CONFIGURATION GENERALE DE L'APPLICATION
 * Projet : Cabinet Comptable
 * Architecture : MVC
 * Backend : PHP natif
 * ==========================================================
 */

// Nom de l'application
define('APP_NAME', 'Cabinet Comptable');

// Adresse e-mail surveillée par l'administrateur du site.
define('ADMIN_CONTACT_EMAIL', app_env('ADMIN_CONTACT_EMAIL', 'admin@cabinet-comptable.local'));

// Version de l'application
define('APP_VERSION', '1.0.0');

// Environnement de travail
define('APP_ENV', app_env('APP_ENV', 'development'));

// Mode débogage
define(
    'APP_DEBUG',
    APP_ENV !== 'production'
        && filter_var(app_env('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOLEAN)
);

// Fuseau horaire
date_default_timezone_set('Africa/Douala');

// Encodage
mb_internal_encoding('UTF-8');

// Chemins de l'application
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEW_PATH', APP_PATH . '/Views');
define('PUBLIC_PATH', ROOT_PATH . '/public');
// Sur Vercel le disque du projet est en lecture seule : seul /tmp est inscriptible.
define('STORAGE_PATH', getenv('VERCEL') !== false ? sys_get_temp_dir() . '/cabinet-storage' : ROOT_PATH . '/storage');

// URL de base
$detectedBaseUrl = null;
if (!empty($_SERVER['HTTP_HOST'])) {
    $forwardedProto = strtolower(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')[0]);
    $scheme = $forwardedProto !== ''
        ? $forwardedProto
        : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
    $detectedBaseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'];
}

// APP_URL (sans slash final) prioritaire ; sinon l'adresse de la requete.
$configuredBaseUrl = app_env('APP_URL');
define(
    'BASE_URL',
    rtrim(
        ($configuredBaseUrl !== null && $configuredBaseUrl !== '')
            ? $configuredBaseUrl
            : ($detectedBaseUrl ?? 'http://localhost/cabinet-comptable/public'),
        '/'
    )
);
if (
    APP_ENV === 'production'
    && parse_url(BASE_URL, PHP_URL_SCHEME) !== 'https'
) {
    throw new RuntimeException('APP_URL doit utiliser HTTPS en production.');
}

// URL des ressources publiques
define('ASSETS_URL', BASE_URL . '/assets');

// Paramètres de session
define('SESSION_NAME', 'CABINET_COMPTABLE_SESSION');
define('SESSION_LIFETIME', max(60, (int) app_env('SESSION_LIFETIME', '3600')));
define(
    'SESSION_SECURE',
    APP_ENV === 'production'
        || filter_var(app_env('SESSION_SECURE', 'false'), FILTER_VALIDATE_BOOLEAN)
);
define(
    'SESSION_HTTP_ONLY',
    APP_ENV === 'production'
        || filter_var(app_env('SESSION_HTTP_ONLY', 'true'), FILTER_VALIDATE_BOOLEAN)
);
define('SESSION_SAME_SITE', app_env('SESSION_SAME_SITE', 'Lax'));
if (!in_array(SESSION_SAME_SITE, ['Strict', 'Lax', 'None'], true)) {
    throw new RuntimeException('SESSION_SAME_SITE doit être Strict, Lax ou None.');
}
if (SESSION_SAME_SITE === 'None' && !SESSION_SECURE) {
    throw new RuntimeException('SameSite=None exige un cookie de session Secure.');
}

// Paramètres régionaux
define('APP_LANGUAGE', 'fr');
define('APP_CURRENCY', 'XAF');
define('APP_DATE_FORMAT', 'd/m/Y');
define('APP_DATETIME_FORMAT', 'd/m/Y H:i:s');

// Pagination
define('PAGINATION_LIMIT', 15);

// Gestion des erreurs
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', 'php://stderr');
}
