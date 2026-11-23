<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Core\Tenant;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use App\Services\TemporaryPassword;

/**
 * Espace du propriétaire de la plateforme : création, suspension et suivi des cabinets clients.
 * L'accès est limité à l'administrateur sans cabinet (voir RouteAccessMiddleware et Tenant::isOwner).
 */
class PlatformController extends Controller
{
    public function index(): void
    {
        if (!Tenant::isOwner()) {
            $this->renderError(403);
            return;
        }

        $this->view('platform.tenants', ['tenants' => Tenant::all()]);
    }

    /** Créer un cabinet client avec son premier administrateur (mot de passe temporaire). */
    public function store(): void
    {
        if (!Tenant::isOwner()) {
            $this->renderError(403);
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $first = trim($_POST['first_name'] ?? '');
        $last = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        $error = null;
        if (mb_strlen($name) < 2 || mb_strlen($name) > 150) {
            $error = 'Indiquez le nom du cabinet (2 à 150 caractères).';
        } elseif ($first === '' || $last === '') {
            $error = 'Indiquez le prénom et le nom de l\'administrateur du cabinet.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Adresse e-mail de l\'administrateur invalide.';
        } elseif (User::findByEmail($email)) {
            $error = 'Cette adresse e-mail est déjà utilisée.';
        }

        $adminRole = Role::findByName(ROLE_ADMIN);
        if ($error === null && !$adminRole) {
            $error = 'Le rôle administrateur est introuvable.';
        }

        if ($error !== null) {
            Session::set('error', $error);
            $this->redirect(url('platform/tenants'));
            return;
        }

        $pdo = Database::connect();
        $plain = TemporaryPassword::generate();

        try {
            $pdo->beginTransaction();
            $pdo->prepare('INSERT INTO tenants (name, status) VALUES (?, ?)')->execute([$name, 'active']);
            $tenantId = (int) $pdo->lastInsertId(defined('DB_DRIVER') && DB_DRIVER === 'pgsql' ? 'tenants_id_seq' : null);
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Création cabinet : ' . $e->getMessage());
            Session::set('error', 'Impossible de créer le cabinet.');
            $this->redirect(url('platform/tenants'));
            return;
        }

        $userId = User::create([
            'first_name' => $first,
            'last_name' => $last,
            'email' => $email,
            'password' => $plain,
            'role_id' => (int) $adminRole['id'],
            'status' => USER_ACTIVE,
            'tenant_id' => $tenantId,
        ]);

        if (!$userId) {
            Session::set('error', 'Le cabinet a été créé mais pas son administrateur : ajoutez-le depuis la liste des utilisateurs.');
            $this->redirect(url('platform/tenants'));
            return;
        }

        User::setMustChangePassword((int) $userId, true);
        AuditService::log('tenant_created', 'tenants', $tenantId, 'Cabinet créé : ' . $name);

        $this->view('admin.users.invitation', [
            'invited' => ['first_name' => $first, 'last_name' => $last, 'email' => $email],
            'plainPassword' => $plain,
            'mustChange' => true,
            'isReset' => false,
            'backUrl' => url('platform/tenants'),
            'backLabel' => 'Retour aux cabinets',
        ]);
    }

    /** Suspendre ou réactiver un cabinet (ses utilisateurs ne peuvent plus se connecter). */
    public function toggle(string $id): void
    {
        if (!Tenant::isOwner()) {
            $this->renderError(403);
            return;
        }

        $pdo = Database::connect();
        $st = $pdo->prepare('SELECT name, status FROM tenants WHERE id = ?');
        $st->execute([(int) $id]);
        $tenant = $st->fetch(\PDO::FETCH_ASSOC);

        if (!$tenant) {
            $this->renderError(404);
            return;
        }

        $new = $tenant['status'] === 'active' ? 'suspended' : 'active';
        $pdo->prepare('UPDATE tenants SET status = ? WHERE id = ?')->execute([$new, (int) $id]);
        AuditService::log('tenant_' . $new, 'tenants', (int) $id, 'Cabinet ' . $tenant['name'] . ($new === 'active' ? ' réactivé' : ' suspendu'));

        Session::set('success', $new === 'active' ? 'Cabinet réactivé.' : 'Cabinet suspendu : ses utilisateurs ne peuvent plus se connecter.');
        $this->redirect(url('platform/tenants'));
    }
}
