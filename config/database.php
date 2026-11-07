
<?php

/**
 * ==========================================================
 * CONFIGURATION DE LA BASE DE DONNEES
 * Projet : Cabinet Comptable
 * SGBD : MySQL / MariaDB ou PostgreSQL (Neon)
 * Connexion : PDO
 * ==========================================================
 */

// Chaîne de connexion unique (ex. Neon) : postgresql://user:motdepasse@hote/base?sslmode=require
$databaseUrl = app_env('DATABASE_URL');
$parsedDatabaseUrl = ($databaseUrl !== null && $databaseUrl !== '')
    ? parse_url($databaseUrl)
    : false;

if ($parsedDatabaseUrl !== false) {
    $urlScheme = strtolower($parsedDatabaseUrl['scheme'] ?? '');
    $dbDriver = in_array($urlScheme, ['postgres', 'postgresql', 'pgsql'], true) ? 'pgsql' : 'mysql';
    $dbHost = $parsedDatabaseUrl['host'] ?? null;
    $dbPort = isset($parsedDatabaseUrl['port'])
        ? (string) $parsedDatabaseUrl['port']
        : ($dbDriver === 'pgsql' ? '5432' : '3306');
    $dbName = isset($parsedDatabaseUrl['path']) ? ltrim(rawurldecode($parsedDatabaseUrl['path']), '/') : null;
    $dbUser = isset($parsedDatabaseUrl['user']) ? rawurldecode($parsedDatabaseUrl['user']) : null;
    $dbPass = isset($parsedDatabaseUrl['pass']) ? rawurldecode($parsedDatabaseUrl['pass']) : null;
    parse_str($parsedDatabaseUrl['query'] ?? '', $dbQuery);
    $dbSslMode = $dbQuery['sslmode'] ?? ($dbDriver === 'pgsql' ? 'require' : '');
} else {
    $dbDriver = strtolower((string) app_env('DB_DRIVER', 'mysql'));
    $dbDriver = in_array($dbDriver, ['pgsql', 'postgres', 'postgresql'], true) ? 'pgsql' : 'mysql';
    $dbHost = app_env('DB_HOST');
    $dbPort = app_env('DB_PORT', $dbDriver === 'pgsql' ? '5432' : '3306');
    $dbName = app_env('DB_DATABASE');
    $dbUser = app_env('DB_USERNAME');
    $dbPass = app_env('DB_PASSWORD');
    $dbSslMode = app_env('DB_SSLMODE', $dbDriver === 'pgsql' ? 'require' : '');
}

if (
    APP_ENV === 'production'
    && (
        $dbHost === null
        || $dbName === null
        || $dbUser === null
        || $dbPass === null
        || $dbPass === ''
        || !filter_var(app_env('MAIL_FROM_ADDRESS', ''), FILTER_VALIDATE_EMAIL)
    )
) {
    throw new RuntimeException('La configuration de production de la base de données ou du courrier est incomplète.');
}

$mailDriver = app_env('MAIL_MAILER', 'php');
if (!in_array($mailDriver, ['php', 'smtp'], true)) {
    throw new RuntimeException('MAIL_MAILER doit être défini à php ou smtp.');
}
if (
    APP_ENV === 'production'
    && $mailDriver === 'smtp'
    && (
        app_env('MAIL_HOST') === null
        || app_env('MAIL_HOST') === ''
        || !ctype_digit(app_env('MAIL_PORT', ''))
        || app_env('MAIL_USERNAME') === null
        || app_env('MAIL_USERNAME') === ''
        || app_env('MAIL_PASSWORD') === null
        || app_env('MAIL_PASSWORD') === ''
    )
) {
    throw new RuntimeException('La configuration SMTP de production est incomplète.');
}

define('DB_DRIVER', $dbDriver);
define('DB_HOST', $dbHost ?? 'localhost');
define('DB_PORT', $dbPort);
define('DB_NAME', $dbName ?? 'cabinet_comptable');
define('DB_USER', $dbUser ?? 'root');
define('DB_PASS', $dbPass ?? '');
define('DB_SSLMODE', $dbSslMode);

// Encodage de la base de données
define('DB_CHARSET', 'utf8mb4');

// Options PDO
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_PERSISTENT         => false
]);
