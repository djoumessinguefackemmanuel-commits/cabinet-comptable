<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Services\PasswordResetService;
use App\Validators\AuthValidator;

class AuthController extends Controller
{
    /**
     * Afficher le formulaire de connexion.
     */
    public function showLogin(): void
    {
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

        if (!Auth::login($data)) {
            $this->render('auth.login', [
                'title' => 'Connexion',
                'errors' => [
                    'Adresse e-mail ou mot de passe incorrect.'
                ],
                'email' => $data['email']
            ], 'layouts.auth');
            return;
        }

        $this->redirectByRole();
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