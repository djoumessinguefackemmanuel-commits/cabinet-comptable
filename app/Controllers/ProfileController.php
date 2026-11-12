<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
use App\Services\AuditService;
use App\Services\AvatarService;
use App\Services\LoginThrottle;
use App\Services\TwoFactorService;

/**
 * Profil de l'utilisateur connecté (administrateur, expert, comptable, client).
 *
 * Chacun ne peut modifier que son propre profil : l'identifiant vient toujours de la session,
 * jamais de la requête. Le rôle, le statut et l'e-mail ne sont pas modifiables ici
 * (ils relèvent de l'administrateur).
 */
class ProfileController extends Controller
{
    public function show(): void
    {
        $user = $this->currentUser();
        if ($user === null) {
            $this->redirect(url('login'));
            return;
        }

        $twoFactor = false;
        try {
            $twoFactor = TwoFactorService::isConfigured() && TwoFactorService::isEnabled((int) $user['id']);
        } catch (\Throwable $exception) {
            // Affichage informatif seulement.
        }

        $_SESSION['user']['avatar_v'] = AvatarService::version((int) $user['id']);

        $this->render('account.profile', [
            'title' => 'Mon profil',
            'profile' => $user,
            'companies' => User::companyNames((int) $user['id']),
            'twoFactor' => $twoFactor,
            'activity' => User::recentActivity((int) $user['id']),
            'avatarVersion' => $_SESSION['user']['avatar_v'],
            'old' => Session::getFlash('profile_old', []),
            'errors' => Session::getFlash('profile_errors', []),
            'passwordErrors' => Session::getFlash('password_errors', []),
        ]);
    }

    public function update(): void
    {
        $user = $this->currentUser();
        if ($user === null) {
            $this->redirect(url('login'));
            return;
        }

        $firstName = $this->clean($_POST['first_name'] ?? '');
        $lastName = $this->clean($_POST['last_name'] ?? '');
        $phone = $this->clean($_POST['phone'] ?? '');
        $errors = [];

        if (mb_strlen($firstName) < 2 || mb_strlen($firstName) > 100) {
            $errors[] = 'Le prénom doit contenir entre 2 et 100 caractères.';
        }
        if (mb_strlen($lastName) < 2 || mb_strlen($lastName) > 100) {
            $errors[] = 'Le nom doit contenir entre 2 et 100 caractères.';
        }
        if ($phone !== '' && preg_match('/^\+?[0-9 ().-]{6,30}$/', $phone) !== 1) {
            $errors[] = 'Le numéro de téléphone est invalide (chiffres, espaces, +, -, parenthèses).';
        }

        if ($errors !== []) {
            Session::set('profile_errors', $errors);
            Session::set('profile_old', ['first_name' => $firstName, 'last_name' => $lastName, 'phone' => $phone]);
            $this->redirect(url('account/profile'));
            return;
        }

        User::updateProfile((int) $user['id'], $firstName, $lastName, $phone === '' ? null : $phone);

        // Mettre à jour le nom affiché sans reconnexion.
        $_SESSION['user']['first_name'] = $firstName;
        $_SESSION['user']['last_name'] = $lastName;
        $_SESSION['user']['phone'] = $phone === '' ? null : $phone;

        AuditService::log('profile_updated', 'users', (int) $user['id'], 'Profil mis à jour par l\'utilisateur.');
        Session::set('success', 'Votre profil a été mis à jour.');
        $this->redirect(url('account/profile'));
    }

    public function uploadAvatar(): void
    {
        $user = $this->currentUser();
        if ($user === null) {
            $this->redirect(url('login'));
            return;
        }

        try {
            AvatarService::store((int) $user['id'], $_FILES['avatar'] ?? []);
            $_SESSION['user']['avatar_v'] = AvatarService::version((int) $user['id']);
            AuditService::log('avatar_updated', 'users', (int) $user['id'], 'Photo de profil modifiée.');
            Session::set('success', 'Votre photo de profil a été mise à jour.');
        } catch (\RuntimeException $exception) {
            Session::set('error', $exception->getMessage());
        } catch (\Throwable $exception) {
            error_log('ProfileController::uploadAvatar : ' . $exception->getMessage());
            Session::set('error', 'Impossible d\'enregistrer la photo pour le moment.');
        }

        $this->redirect(url('account/profile'));
    }

    public function deleteAvatar(): void
    {
        $user = $this->currentUser();
        if ($user === null) {
            $this->redirect(url('login'));
            return;
        }

        AvatarService::delete((int) $user['id']);
        $_SESSION['user']['avatar_v'] = null;
        AuditService::log('avatar_deleted', 'users', (int) $user['id'], 'Photo de profil supprimée.');
        Session::set('success', 'Votre photo de profil a été supprimée.');
        $this->redirect(url('account/profile'));
    }

    /**
     * Afficher une photo : la sienne, ou celle de n'importe quel utilisateur pour un administrateur.
     */
    public function avatar(string $id): void
    {
        $targetId = (int) $id;
        if ($targetId !== (int) Auth::id() && !Auth::hasRole(ROLE_ADMIN)) {
            http_response_code(403);
            exit;
        }

        $avatar = AvatarService::read($targetId);
        if ($avatar === null) {
            http_response_code(404);
            exit;
        }

        header('Content-Type: image/jpeg');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=86400');
        header('Content-Length: ' . strlen($avatar['content']));
        echo $avatar['content'];
        exit;
    }

    public function changePassword(): void
    {
        $user = $this->currentUser();
        if ($user === null) {
            $this->redirect(url('login'));
            return;
        }

        $userId = (int) $user['id'];
        $throttleKey = 'profil:' . $userId;
        $current = is_string($_POST['current_password'] ?? null) ? $_POST['current_password'] : '';
        $new = is_string($_POST['new_password'] ?? null) ? $_POST['new_password'] : '';
        $confirm = is_string($_POST['new_password_confirmation'] ?? null) ? $_POST['new_password_confirmation'] : '';

        if (LoginThrottle::isBlocked($throttleKey)) {
            http_response_code(429);
            Session::set('password_errors', ['Trop de tentatives. Réessayez dans quelques minutes.']);
            $this->redirect(url('account/profile'));
            return;
        }

        if (!password_verify($current, (string) $user['password'])) {
            LoginThrottle::recordFailure($throttleKey);
            Session::set('password_errors', ['Le mot de passe actuel est incorrect.']);
            $this->redirect(url('account/profile'));
            return;
        }

        $errors = [];
        if (strlen($new) < 10) {
            $errors[] = 'Le nouveau mot de passe doit contenir au moins 10 caractères.';
        }
        if (strlen($new) > 72) {
            $errors[] = 'Le nouveau mot de passe ne doit pas dépasser 72 caractères.';
        }
        if ($new !== $confirm) {
            $errors[] = 'La confirmation ne correspond pas au nouveau mot de passe.';
        }
        if ($new === $current) {
            $errors[] = 'Le nouveau mot de passe doit être différent de l\'ancien.';
        }
        if (preg_match('/[A-Za-z]/', $new) !== 1 || preg_match('/\d/', $new) !== 1) {
            $errors[] = 'Le nouveau mot de passe doit contenir au moins une lettre et un chiffre.';
        }

        if ($errors !== []) {
            Session::set('password_errors', $errors);
            $this->redirect(url('account/profile'));
            return;
        }

        User::updatePassword($userId, $new);
        LoginThrottle::clear($throttleKey);
        session_regenerate_id(true);

        AuditService::log('password_changed', 'users', $userId, 'Mot de passe modifié par l\'utilisateur.');
        Session::set('success', 'Votre mot de passe a été modifié.');
        $this->redirect(url('account/profile'));
    }

    /**
     * Utilisateur à jour depuis la base (et toujours actif).
     */
    private function currentUser(): ?array
    {
        $id = Auth::id();
        if ($id === null) {
            return null;
        }

        $user = User::findById($id);

        return $user && ($user['status'] ?? '') === USER_ACTIVE ? $user : null;
    }

    private function clean(mixed $value): string
    {
        return is_string($value) ? trim(preg_replace('/\s+/', ' ', $value) ?? '') : '';
    }
}
