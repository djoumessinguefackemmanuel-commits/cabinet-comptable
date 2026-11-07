
<?php

/**
 * ==========================================================
 * CONFIGURATION DE LA BASE DE DONNEES
 * Projet : Cabinet Comptable
 * SGBD : MySQL / MariaDB
 * Connexion : PDO
 * ==========================================================
 */

if (
    APP_ENV === 'production'
    && (
        app_env('DB_HOST') === null
        || app_env('DB_DATABASE') === null
        || app_env('DB_USERNAME') === null
        || app_env('DB_PASSWORD') === null
        || app_env('DB_PASSWORD') === ''
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

define('DB_HOST', app_env('DB_HOST', 'localhost'));
define('DB_PORT', app_env('DB_PORT', '3306'));
define('DB_NAME', app_env('DB_DATABASE', 'cabinet_comptable'));
define('DB_USER', app_env('DB_USERNAME', 'root'));
define('DB_PASS', app_env('DB_PASSWORD', ''));

// Encodage de la base de données
define('DB_CHARSET', 'utf8mb4');

// Options PDO
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_PERSISTENT         => false
]);
