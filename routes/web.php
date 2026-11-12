<?php

use App\Controllers\AuthController;
use App\Controllers\AccountSecurityController;
use App\Controllers\ProfileController;
use App\Controllers\DashboardController;
use App\Controllers\UserController;
use App\Controllers\RoleController;
use App\Controllers\CompanyController;
use App\Controllers\ClientController;
use App\Controllers\ExerciseController;
use App\Controllers\AccountController;
use App\Controllers\JournalController;
use App\Controllers\EntryController;
use App\Controllers\LedgerController;
use App\Controllers\BalanceController;
use App\Controllers\ReconciliationController;
use App\Controllers\TaxController;
use App\Controllers\FinancialStatementController;
use App\Controllers\DocumentController;
use App\Controllers\NotificationController;
use App\Controllers\AuditController;
use App\Controllers\InvoiceController;
use App\Controllers\SettingController;
use App\Controllers\SupportController;

/*
|--------------------------------------------------------------------------
| Routes publiques
|--------------------------------------------------------------------------
*/

$router->get('/', [AuthController::class, 'showLogin']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/login/2fa', [AuthController::class, 'showTwoFactor']);
$router->post('/login/2fa', [AuthController::class, 'verifyTwoFactor']);

$router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
$router->post('/forgot-password', [AuthController::class, 'forgotPassword']);

$router->get('/reset-password', [AuthController::class, 'showResetPassword']);
$router->post('/reset-password', [AuthController::class, 'resetPassword']);

$router->get('/contact-admin', [SupportController::class, 'showContactForm']);
$router->post('/contact-admin', [SupportController::class, 'submitContactRequest']);

$router->post('/logout', [AuthController::class, 'logout']);

/*
|--------------------------------------------------------------------------
| Sécurité du compte (double authentification)
|--------------------------------------------------------------------------
*/

$router->get('/account/profile', [ProfileController::class, 'show']);
$router->post('/account/profile', [ProfileController::class, 'update']);
$router->post('/account/profile/password', [ProfileController::class, 'changePassword']);
$router->post('/account/profile/avatar', [ProfileController::class, 'uploadAvatar']);
$router->post('/account/profile/avatar/delete', [ProfileController::class, 'deleteAvatar']);
$router->get('/account/avatar/{id}', [ProfileController::class, 'avatar']);
$router->get('/account/security', [AccountSecurityController::class, 'show']);
$router->post('/account/security/enable', [AccountSecurityController::class, 'enable']);
$router->post('/account/security/disable', [AccountSecurityController::class, 'disable']);
$router->post('/account/security/recovery-codes', [AccountSecurityController::class, 'regenerateCodes']);

/*
|--------------------------------------------------------------------------
| Tableau de bord
|--------------------------------------------------------------------------
*/

$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/admin/dashboard', [DashboardController::class, 'index']);
$router->get('/expert/dashboard', [DashboardController::class, 'index']);
$router->get('/accountant/dashboard', [DashboardController::class, 'index']);
$router->get('/client/dashboard', [DashboardController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Utilisateurs et rôles
|--------------------------------------------------------------------------
*/

$router->get('/users', [UserController::class, 'index']);
$router->get('/users/create', [UserController::class, 'create']);
$router->post('/users/store', [UserController::class, 'store']);
$router->get('/users/{id}/edit', [UserController::class, 'edit']);
$router->post('/users/{id}/update', [UserController::class, 'update']);
$router->post('/users/{id}/status', [UserController::class, 'updateStatus']);
$router->post('/users/{id}/delete', [UserController::class, 'delete']);
$router->post('/users/{id}/two-factor/reset', [AccountSecurityController::class, 'adminReset']);

$router->get('/roles', [RoleController::class, 'index']);
$router->get('/roles/{id}', [RoleController::class, 'show']);
$router->post('/roles/assign', [RoleController::class, 'assign']);

/*
|--------------------------------------------------------------------------
| Entreprises et clients
|--------------------------------------------------------------------------
*/

$router->get('/companies', [CompanyController::class, 'index']);
$router->get('/companies/create', [CompanyController::class, 'create']);
$router->post('/companies/store', [CompanyController::class, 'store']);
$router->get('/companies/{id}', [CompanyController::class, 'show']);
$router->get('/companies/{id}/edit', [CompanyController::class, 'edit']);
$router->post('/companies/{id}/update', [CompanyController::class, 'update']);
$router->post('/companies/{id}/delete', [CompanyController::class, 'delete']);

$router->get('/clients', [ClientController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Exercices comptables
|--------------------------------------------------------------------------
*/

$router->get('/exercises', [ExerciseController::class, 'index']);
$router->get('/exercises/create', [ExerciseController::class, 'create']);
$router->post('/exercises/store', [ExerciseController::class, 'store']);
$router->get('/exercises/{id}/edit', [ExerciseController::class, 'edit']);
$router->post('/exercises/{id}/update', [ExerciseController::class, 'update']);
$router->post('/exercises/{id}/delete', [ExerciseController::class, 'delete']);

/*
|--------------------------------------------------------------------------
| Comptes et journaux
|--------------------------------------------------------------------------
*/

$router->get('/accounting/accounts', [AccountController::class, 'index']);
$router->get('/accounting/accounts/create', [AccountController::class, 'create']);
$router->post('/accounting/accounts/store', [AccountController::class, 'store']);
$router->post('/accounting/accounts', [AccountController::class, 'store']);

$router->get('/accounting/journals', [JournalController::class, 'index']);
$router->get('/accounting/journals/create', [JournalController::class, 'create']);
$router->post('/accounting/journals/store', [JournalController::class, 'store']);
$router->post('/accounting/journals', [JournalController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Écritures comptables
|--------------------------------------------------------------------------
*/

$router->get('/accounting/entries', [EntryController::class, 'index']);
$router->get('/accounting/entries/create', [EntryController::class, 'create']);
$router->post('/accounting/entries/store', [EntryController::class, 'store']);
$router->post('/accounting/entries', [EntryController::class, 'store']);
$router->get('/accounting/entries/{id}', [EntryController::class, 'show']);
$router->post('/accounting/entries/{id}/validate', [EntryController::class, 'validate']);
$router->post('/accounting/entries/{id}/cancel', [EntryController::class, 'cancel']);

/*
|--------------------------------------------------------------------------
| Grand livre et balance
|--------------------------------------------------------------------------
*/

$router->get('/accounting/ledger', [LedgerController::class, 'index']);
$router->get('/accounting/balance', [BalanceController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Rapprochement bancaire
|--------------------------------------------------------------------------
*/

$router->get('/accounting/reconciliation', [ReconciliationController::class, 'index']);
$router->post('/accounting/reconciliation/{id}/reconcile', [ReconciliationController::class, 'reconcile']);
$router->post('/accounting/reconciliation/{id}/unreconcile', [ReconciliationController::class, 'unreconcile']);

/*
|--------------------------------------------------------------------------
| Fiscalité
|--------------------------------------------------------------------------
*/

$router->get('/taxes', [TaxController::class, 'index']);
$router->get('/taxes/vat', [TaxController::class, 'vat']);
$router->get('/taxes/declarations', [TaxController::class, 'declarations']);
$router->get('/taxes/deadlines', [TaxController::class, 'deadlines']);
$router->post('/taxes/calculate', [TaxController::class, 'calculate']);

/*
|--------------------------------------------------------------------------
| États financiers
|--------------------------------------------------------------------------
*/

$router->get('/reports', function (): void {
    header('Location: ' . url('reports/balance-sheet'));
    exit;
});
$router->get('/reports/balance-sheet', [FinancialStatementController::class, 'balanceSheet']);
$router->get('/reports/income-statement', [FinancialStatementController::class, 'incomeStatement']);
$router->get('/reports/trial-balance', [FinancialStatementController::class, 'trialBalance']);
$router->get('/reports/general-ledger', [FinancialStatementController::class, 'generalLedger']);

/*
|--------------------------------------------------------------------------
| Documents et factures
|--------------------------------------------------------------------------
*/

$router->get('/documents', [DocumentController::class, 'index']);
$router->post('/documents/upload', [DocumentController::class, 'upload']);
$router->get('/documents/{id}/download', [DocumentController::class, 'download']);
$router->post('/documents/{id}/delete', [DocumentController::class, 'delete']);

$router->get('/invoices', [InvoiceController::class, 'index']);
$router->post('/invoices/store', [InvoiceController::class, 'store']);
$router->post('/invoices/{id}/status', [InvoiceController::class, 'updateStatus']);
$router->post('/invoices/{id}/delete', [InvoiceController::class, 'delete']);

$router->post('/companies', [CompanyController::class, 'store']);
$router->post('/exercises', [ExerciseController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Notifications
|--------------------------------------------------------------------------
*/

$router->get('/notifications', [NotificationController::class, 'index']);
$router->post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
$router->post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

/*
|--------------------------------------------------------------------------
| Audit et paramètres
|--------------------------------------------------------------------------
*/

$router->get('/audit', [AuditController::class, 'index']);

$router->get('/settings', [SettingController::class, 'index']);
$router->post('/settings/update', [SettingController::class, 'update']);

$router->get('/admin/support', [SupportController::class, 'index']);
$router->post('/admin/support/{id}/status', [SupportController::class, 'updateStatus']);
