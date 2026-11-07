<?php

/**
 * ==========================================================
 * FONCTIONS UTILITAIRES DE L'APPLICATION
 * Projet : Cabinet Comptable
 * Architecture : MVC
 * ==========================================================
 */

if (!function_exists('e')) {

    /**
     * Sécuriser l'affichage d'une valeur HTML.
     */
    function e($value): string
    {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES,
            'UTF-8'
        );
    }
}

if (!function_exists('url')) {

    /**
     * Générer une URL interne.
     */
    function url(string $path = ''): string
    {
        return rtrim(BASE_URL, '/') . '/'
             . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {

    /**
     * Générer l'URL d'une ressource publique.
     */
    function asset(string $path): string
    {
        return rtrim(ASSETS_URL, '/') . '/'
             . ltrim($path, '/');
    }
}

if (!function_exists('old')) {

    /**
     * Récupérer une ancienne valeur de formulaire.
     */
    function old(string $key, $default = '') 
    {
        return $_SESSION['_old'][$key] ?? $default;
    }
}

if (!function_exists('formatMoney')) {

    /**
     * Formater un montant en francs CFA.
     */
    function formatMoney($amount): string
    {
        return number_format(
            (float) $amount,
            0,
            ',',
            ' '
        ) . ' FCFA';
    }
}

if (!function_exists('formatDate')) {

    /**
     * Formater une date.
     */
    function formatDate(?string $date): string
    {
        if (empty($date)) {
            return '';
        }

        $timestamp = strtotime($date);

        if ($timestamp === false) {
            return '';
        }

        return date(APP_DATE_FORMAT, $timestamp);
    }
}

if (!function_exists('formatDateTime')) {

    /**
     * Formater une date avec l'heure.
     */
    function formatDateTime(?string $date): string
    {
        if (empty($date)) {
            return '';
        }

        $timestamp = strtotime($date);

        if ($timestamp === false) {
            return '';
        }

        return date(APP_DATETIME_FORMAT, $timestamp);
    }
}

if (!function_exists('csrfToken')) {

    /**
     * Générer ou récupérer le jeton CSRF.
     */
    function csrfToken(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(
                random_bytes(32)
            );
        }

        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('csrfField')) {

    /**
     * Générer le champ HTML du jeton CSRF.
     */
    function csrfField(): string
    {
        return '<input type="hidden" name="_token" value="'
            . e(csrfToken())
            . '">';
    }
}
