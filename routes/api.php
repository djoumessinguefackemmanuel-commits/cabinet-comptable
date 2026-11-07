
<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\CompanyController;
use App\Controllers\ExerciseController;
use App\Controllers\AccountController;
use App\Controllers\JournalController;
use App\Controllers\EntryController;
use App\Controllers\LedgerController;
use App\Controllers\BalanceController;
use App\Controllers\TaxController;
use App\Controllers\NotificationController;
use App\Controllers\ApiEntryController;

/*
|--------------------------------------------------------------------------
| Routes API - Authentification
|--------------------------------------------------------------------------
*/

$router->post('/api/login', [AuthController::class, 'login']);
$router->post('/api/logout', [AuthController::class, 'logout']);

/*
|--------------------------------------------------------------------------
| Routes API - Tableau de bord
|--------------------------------------------------------------------------
*/

$router->get('/api/dashboard', [DashboardController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Routes API - Entreprises
|--------------------------------------------------------------------------
*/

$router->get('/api/companies', [CompanyController::class, 'index']);
$router->post('/api/companies', [CompanyController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Routes API - Exercices comptables
|--------------------------------------------------------------------------
*/

$router->get('/api/exercises', [ExerciseController::class, 'index']);
$router->post('/api/exercises', [ExerciseController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Routes API - Comptes comptables
|--------------------------------------------------------------------------
*/

$router->get('/api/accounts', [AccountController::class, 'index']);
$router->post('/api/accounts', [AccountController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Routes API - Journaux
|--------------------------------------------------------------------------
*/

$router->get('/api/journals', [JournalController::class, 'index']);
$router->post('/api/journals', [JournalController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Routes API - Écritures comptables
|--------------------------------------------------------------------------
*/

$router->get('/api/entries', [EntryController::class, 'index']);
$router->post('/api/entries', [EntryController::class, 'store']);
$router->get('/api/entries/{id}', [ApiEntryController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Routes API - Grand livre et balance
|--------------------------------------------------------------------------
*/

$router->get('/api/ledger', [LedgerController::class, 'index']);
$router->get('/api/balance', [BalanceController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Routes API - Fiscalité
|--------------------------------------------------------------------------
*/

$router->get('/api/taxes', [TaxController::class, 'index']);
$router->post('/api/taxes/calculate', [TaxController::class, 'calculate']);

/*
|--------------------------------------------------------------------------
| Routes API - Notifications
|--------------------------------------------------------------------------
*/

$router->get('/api/notifications', [NotificationController::class, 'index']);
$router->post('/api/notifications/read', [NotificationController::class, 'markAllAsReadApi']);
