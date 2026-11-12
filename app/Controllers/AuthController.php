<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Services\AuditService;
use App\Models\User;
use App\Services\LoginThrottle;
use App\Services\TwoFactorService;
use App\Services\PasswordResetService;
use App\Validators\AuthValidator;

class AuthController extends Controller
{
    /**
     * Afficher le formulaire de connexion.
     */
    public function showLogin(): void
    {
        Session::remove('two_factor_pending');

        if (Auth::check()) {
            $this->redirectByRole();
            return;
        }

        $this->render('auth.login', [
            'title' => 'Connexion'
        ], 'layouts.auth');
    }

    /**
     * Traiter la demande de connexion.
     */
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(url('login'));
            return;
        }

        $data = [
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? ''
        ];

        $validation = AuthValidator::validateLogin($data);

        if (!$validation['valid']) {
            $this->render('auth.login', [
                'title' => 'Connexion',
                'errors' => array_values($validation['errors']),
                'email' => $data['email']
            ], 'layouts.auth');
            return;
        }

        if (LoginThrottle::isBlocked($data['email'])) {
            http_response_code(429);
            $this->render('auth.login', [
                'title' => 'Connexion',
                'errors' => [
                    'Trop de tentatives de connexion. Réessayez dans quelques minutes.'
                ],
                'email' => $data['email']
            ], 'layouts.auth');
            return;
        }

        if (!Auth::login($data)) {
            LoginThrottle::recordFailure($data['email']);
            $this->render('auth.login', [
                'title' => 'Connexion',
                'errors' => [
                    'Adresse e-mail ou mot de passe incorrect.'
                ],
                'email' => $data['email']
            ], 'layouts.auth');
            return;
        }

        // Seconde étape : le mot de passe seul ne suffit pas si la 2FA est activée.
        $loggedUser = Auth::user();
        try {
            $needsSecondStep = $loggedUser !== null
                && TwoFactorService::isEnabled((int) $loggedUser['id']);
        } catch (\Throwable $exception) {
            // En cas de doute, on refuse la connexion plutôt que de contourner la 2FA.
            Auth::logout();
            $this->render('auth.login', [
                'title' => 'Connexion',
                'errors' => ['Service momentanément indisponible. Réessayez dans un instant.'],
                'email' => $data['email']
            ], 'layouts.auth');
            return;
        }

        if ($needsSecondStep) {
            Session::set('two_factor_pending', [
                'user' => $loggedUser,
                'email' => $data['email'],
                'expires' => time() + 300,
                'attempts' => 0
            ]);
            Session::remove('user');
            $this->redirect(url('login/2fa'));
            return;
        }

        LoginThrottle::clear($data['email']);
        User::touchLastLogin((int) $loggedUser['id']);

        $this->redirectByRole();
    }

    /**
     * Afficher la saisie du code de double authentification.
     */
    public function showTwoFactor(): void
    {
        if (!$this->twoFactorPending()) {
            $this->redirect(url('login'));
            return;
        }

        $this->render('auth.two-factor', [
            'title' => 'Vérification en deux étapes'
        ], 'layouts.auth');
    }

    /**
     * Vérifier le code de double authentification et finaliser la connexion.
     */
    public function verifyTwoFactor(): void
    {
        $pending = $this->twoFactorPending();
        if ($pending === null) {
            Session::set('error', 'Votre tentative de connexion a expiré. Veuillez recommencer.');
            $this->redirect(url('login'));
            return;
        }

        $email = (string) ($pending['email'] ?? '');
        $code = is_string($_POST['code'] ?? null) ? trim($_POST['code']) : '';
        $userId = (int) ($pending['user']['id'] ?? 0);

        if (LoginThrottle::isBlocked($email)) {
            http_response_code(429);
            $this->render('auth.two-factor', [
                'title' => 'Vérification en deux étapes',
                'errors' => ['Trop de tentatives. Réessayez dans quelques minutes.']
            ], 'layouts.auth');
            return;
        }

        $valid = false;
        try {
            $valid = $code !== '' && TwoFactorService::verifyLogin($userId, $code);
        } catch (\Throwable $exception) {
            error_log('2FA : ' . $exception->getMessage());
        }

        if (!$valid) {
            LoginThrottle::recordFailure($email);
            $pending['attempts'] = (int) ($pending['attempts'] ?? 0) + 1;

            if ($pending['attempts'] >= 5) {
                Session::remove('two_factor_pending');
                Session::set('error', 'Trop de codes incorrects. Veuillez vous reconnecter.');
                $this->redirect(url('login'));
                return;
            }

            Session::set('two_factor_pending', $pending);
            $this->render('auth.two-factor', [
                'title' => 'Vérification en deux étapes',
                'errors' => ['Code incorrect ou déjà utilisé.']
            ], 'layouts.auth');
            return;
        }

        session_regenerate_id(true);
        Session::set('user', $pending['user']);
        Session::remove('two_factor_pending');
        LoginThrottle::clear($email);
        AuditService::log('login_2fa', 'users', $userId, 'Connexion avec double authentification.');
        User::touchLastLogin($userId);

        $this->redirectByRole();
    }

    /**
     * Connexion en attente de la seconde étape (valide 5 minutes).
     */
    private function twoFactorPending(): ?array
    {
        $pending = Session::get('two_factor_pending');

        if (!is_array($pending) || (int) ($pending['expires'] ?? 0) < time()) {
            Session::remove('two_factor_pending');
            return null;
        }

        return $pending;
    }

    /**
     * Déconnecter l'utilisateur.
     */
    public function logout(): void
    {
        Auth::logout();

        Session::set('success', 'Vous avez été déconnecté avec succès.');

        $this->redirect(url('login'));
    }

    /**
     * Afficher le formulaire de mot de passe oublié.
     */
    public function showForgotPassword(): void
    {
        $this->render('auth.forgot-password', [
            'title' => 'Mot de passe oublié'
        ], 'layouts.auth');
    }

    /**
     * Afficher le formulaire de réinitialisation du mot de passe.
     */
    public function showResetPassword(): void
    {
        $token = $_GET['token'] ?? '';

        $this->render('auth.reset-password', [
            'title' => 'Réinitialisation du mot de passe',
            'token' => $token
        ], 'layouts.auth');
    }

    public function forgotPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(url('forgot-password'));
            return;
        }

        $emailInput = $_POST['email'] ?? '';
        $email = is_string($emailInput) ? trim($emailInput) : '';
        $validation = AuthValidator::validateForgotPassword(['email' => $email]);
        if (!$validation['valid']) {
            $this->render('auth.forgot-password', [
                'title' => 'Mot de passe oublié',
                'errors' => array_values($validation['errors']),
                'email' => $email
            ], 'layouts.auth');
            return;
        }

        (new PasswordResetService())->request($email);
        Session::set(
            'warning',
            'Si cette adresse est associée à un compte, un message sera envoyé. Si vous ne le recevez pas, contactez l’administrateur.'
        );

        $this->redirect(url('forgot-password'));
    }

    public function resetPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(url('reset-password'));
            return;
        }

        $data = [
            'token' => is_string($_POST['token'] ?? null)
                ? trim($_POST['token'])
                : '',
            'password' => is_string($_POST['password'] ?? null)
                ? $_POST['password']
                : '',
            'password_confirmation' => is_string($_POST['password_confirmation'] ?? null)
                ? $_POST['password_confirmation']
                : ''
        ];
        $validation = AuthValidator::validateResetPassword($data);

        if (!$validation['valid']) {
            $this->render('auth.reset-password', [
                'title' => 'Réinitialisation du mot de passe',
                'token' => $data['token'],
                'errors' => array_values($validation['errors'])
            ], 'layouts.auth');
            return;
        }

        if (!(new PasswordResetService())->reset($data['token'], $data['password'])) {
            Session::set(
                'error',
                'Ce lien de réinitialisation est invalide ou a expiré. Faites une nouvelle demande.'
            );
            $this->redirect(url('forgot-password'));
            return;
        }

        Session::set('success', 'Votre mot de passe a été modifié. Vous pouvez vous connecter.');
        $this->redirect(url('login'));
    }

    /**
     * Rediriger l'utilisateur selon son rôle.
     */
    private function redirectByRole(): void
    {
        $user = Auth::user();

        if (!$user) {
            $this->redirect(url('login'));
            return;
        }

        switch ($user['role'] ?? '') {

            case ROLE_ADMIN:
                $this->redirect(url('admin/dashboard'));
                break;

            case ROLE_EXPERT:
                $this->redirect(url('expert/dashboard'));
                break;

            case ROLE_ACCOUNTANT:
                $this->redirect(url('accountant/dashboard'));
                break;

            case ROLE_CLIENT:
                $this->redirect(url('client/dashboard'));
                break;

            default:
                Auth::logout();
                $this->redirect(url('login'));
                break;
        }
    }
}