<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
use App\Services\AuditService;
use App\Services\Totp;
use App\Services\TwoFactorService;

/**
 * Sécurité du compte connecté : double authentification (TOTP) et codes de secours.
 */
class AccountSecurityController extends Controller
{
    public function show(): void
    {
        $userId = (int) Auth::id();
        $configured = TwoFactorService::isConfigured();
        $enabled = false;
        $remaining = 0;
        $setupSecret = null;
        $setupUri = null;

        if ($configured) {
            $enabled = TwoFactorService::isEnabled($userId);

            if ($enabled) {
                $remaining = TwoFactorService::remainingRecoveryCodes($userId);
            } else {
                $setupSecret = Session::get('two_factor_setup_secret');
                if (!is_string($setupSecret) || $setupSecret === '') {
                    $setupSecret = Totp::generateSecret();
                    Session::set('two_factor_setup_secret', $setupSecret);
                }

                $user = Auth::user();
                $setupUri = Totp::uri($setupSecret, (string) ($user['email'] ?? ''), APP_NAME);
            }
        }

        $this->render('account.security', [
            'title' => 'Sécurité du compte',
            'configured' => $configured,
            'enabled' => $enabled,
            'remaining' => $remaining,
            'setupSecret' => $setupSecret,
            'setupUri' => $setupUri,
            'newCodes' => Session::getFlash('two_factor_new_codes')
        ]);
    }

    public function enable(): void
    {
        $userId = (int) Auth::id();
        $secret = Session::get('two_factor_setup_secret');
        $code = is_string($_POST['code'] ?? null) ? trim($_POST['code']) : '';

        if (!TwoFactorService::isConfigured() || !is_string($secret) || $secret === '') {
            Session::set('error', 'La configuration a expiré. Veuillez recommencer.');
            $this->redirect(url('account/security'));
            return;
        }

        $codes = TwoFactorService::enable($userId, $secret, $code);
        if ($codes === null) {
            Session::set('error', 'Code incorrect. Vérifiez l\'heure de votre téléphone et réessayez.');
            $this->redirect(url('account/security'));
            return;
        }

        Session::remove('two_factor_setup_secret');
        Session::set('two_factor_new_codes', $codes);
        AuditService::log('two_factor_enabled', 'users', $userId, 'Double authentification activée.');
        Session::set('success', 'Double authentification activée. Conservez vos codes de secours.');
        $this->redirect(url('account/security'));
    }

    public function disable(): void
    {
        $userId = (int) Auth::id();

        if (!$this->confirmIdentity()) {
            $this->redirect(url('account/security'));
            return;
        }

        TwoFactorService::disable($userId);
        AuditService::log('two_factor_disabled', 'users', $userId, 'Double authentification désactivée.');
        Session::set('success', 'Double authentification désactivée.');
        $this->redirect(url('account/security'));
    }

    public function regenerateCodes(): void
    {
        $userId = (int) Auth::id();

        if (!$this->confirmIdentity()) {
            $this->redirect(url('account/security'));
            return;
        }

        Session::set('two_factor_new_codes', TwoFactorService::regenerateRecoveryCodes($userId));
        AuditService::log('two_factor_codes', 'users', $userId, 'Codes de secours régénérés.');
        Session::set('success', 'Nouveaux codes de secours générés. Les anciens ne fonctionnent plus.');
        $this->redirect(url('account/security'));
    }

    /**
     * Réinitialisation par un administrateur (téléphone perdu).
     */
    public function adminReset(string $id): void
    {
        if (!Auth::hasRole(ROLE_ADMIN)) {
            $this->renderError(403);
            return;
        }

        $target = User::findById((int) $id);
        if (!$target) {
            $this->renderError(404);
            return;
        }

        TwoFactorService::disable((int) $id);
        AuditService::log(
            'two_factor_reset',
            'users',
            (int) $id,
            'Double authentification réinitialisée par un administrateur pour ' . ($target['email'] ?? '')
        );
        Session::set('success', 'La double authentification de ' . ($target['email'] ?? 'cet utilisateur') . ' a été réinitialisée.');
        $this->redirect(url('users'));
    }

    /**
     * Demander mot de passe + code actuel avant une action sensible.
     */
    private function confirmIdentity(): bool
    {
        $userId = (int) Auth::id();
        $current = Auth::user();
        $record = User::findByEmail((string) ($current['email'] ?? ''));
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $code = is_string($_POST['code'] ?? null) ? trim($_POST['code']) : '';

        if (!$record || !password_verify($password, (string) $record['password'])) {
            Session::set('error', 'Mot de passe incorrect.');
            return false;
        }

        try {
            $ok = $code !== '' && TwoFactorService::verifyLogin($userId, $code);
        } catch (\Throwable $exception) {
            error_log('2FA : ' . $exception->getMessage());
            $ok = false;
        }

        if (!$ok) {
            Session::set('error', 'Code de vérification incorrect ou déjà utilisé.');
            return false;
        }

        return true;
    }
}
