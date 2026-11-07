<?php
namespace App\Core;

use App\Models\User;
use App\Models\UserCompany;

class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user']['id'])
            ? (int) $_SESSION['user']['id']
            : null;
    }

    /**
     * Récupérer les entreprises accessibles; null signifie accès global.
     */
    public static function companyIds(): ?array
    {
        if (!self::check()) {
            return [];
        }

        $role = $_SESSION['user']['role'] ?? '';
        if (in_array($role, [ROLE_ADMIN, ROLE_EXPERT], true)) {
            return null;
        }

        $userId = self::id();
        return $userId === null
            ? []
            : UserCompany::getCompanyIdsByUser($userId);
    }

    public static function login(array $credentials): bool
    {
        $user = User::findByEmail($credentials['email']);

        if (!$user) {
            return false;
        }

        if (!password_verify($credentials['password'], $user['password'])) {
            return false;
        }

        if ($user['status'] !== USER_ACTIVE) {
            return false;
        }

        session_regenerate_id(true);

        unset($user['password']);

        $_SESSION['user'] = $user;

        return true;
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);

        session_regenerate_id(true);
    }

    public static function hasRole(string $role): bool
    {
        return isset($_SESSION['user']['role'])
            && $_SESSION['user']['role'] === $role;
    }

    public static function can(string $permission): bool
    {
        if (!self::check()) {
            return false;
        }

        $role = $_SESSION['user']['role'] ?? null;

        $permissions = require CONFIG_PATH . '/permissions.php';

        return in_array(
            $permission,
            $permissions[$role] ?? [],
            true
        );
    }
}
