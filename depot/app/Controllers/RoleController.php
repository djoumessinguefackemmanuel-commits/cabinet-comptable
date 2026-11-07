<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Role;

class RoleController extends Controller
{
    /**
     * Afficher la liste des rôles.
     */
    public function index(): void
    {
        $roles = Role::getAll();

        $this->view('admin.roles.index', [
            'roles' => $roles
        ]);
    }

    /**
     * Afficher les détails d'un rôle.
     */
    public function show(string $id): void
    {
        $role = Role::findById((int) $id);

        if (!$role) {
            $this->renderError(404);
            return;
        }

        $this->view('admin.roles.show', [
            'role' => $role
        ]);
    }

    /**
     * Attribuer un rôle à un utilisateur.
     */
    public function assign(): void
    {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $roleId = (int) ($_POST['role_id'] ?? 0);

        if ($userId <= 0 || $roleId <= 0) {
            Session::set('error', 'Les informations fournies sont invalides.');
            $this->redirect(url('admin/users'));
            return;
        }

        $role = Role::findById($roleId);

        if (!$role) {
            Session::set('error', 'Le rôle sélectionné est introuvable.');
            $this->redirect(url('admin/users'));
            return;
        }

        if (!Role::assignToUser($userId, $roleId)) {
            Session::set('error', 'Impossible d’attribuer ce rôle.');
            $this->redirect(url('admin/users'));
            return;
        }

        Session::set('success', 'Rôle attribué avec succès.');

        $this->redirect(url('admin/users'));
    }
}