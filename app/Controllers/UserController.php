<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Role;
use App\Models\User;
use App\Models\UserCompany;
use App\Models\Company;
use App\Validators\UserValidator;

class UserController extends Controller
{
    /**
     * Afficher la liste des utilisateurs.
     */
    public function index(): void
    {
        if (!$this->ensureAdmin()) {
            return;
        }

        $users = User::getAll();

        $this->view('admin.users.index', [
            'users' => $users
        ]);
    }

    /**
     * Afficher le formulaire de création.
     */
    public function create(): void
    {
        if (!$this->ensureAdmin()) {
            return;
        }

        $this->view('admin.users.create', [
            'roles' => Role::getAll()
        ]);
    }

    /**
     * Enregistrer un nouvel utilisateur.
     */
    public function store(): void
    {
        if (!$this->ensureAdmin()) {
            return;
        }

        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'password_confirmation' => $_POST['password_confirmation'] ?? '',
            'role_id' => $_POST['role_id'] ?? '',
            'status' => $_POST['status'] ?? USER_ACTIVE
        ];

        $validation = UserValidator::validateCreate($data);

        if (!$validation['valid']) {
            unset($data['password'], $data['password_confirmation']);
            $this->view('admin.users.create', [
                'errors' => $validation['errors'],
                'old' => $data,
                'roles' => Role::getAll()
            ]);
            return;
        }

        unset($data['password_confirmation']);
        $data['role_id'] = (int) $data['role_id'];

        try {
            $createdUserId = User::create($data);
        } catch (\PDOException $exception) {
            if (($exception->errorInfo[1] ?? null) !== 1062) {
                throw $exception;
            }

            unset($data['password']);
            $this->view('admin.users.create', [
                'errors' => ['Cette adresse e-mail est déjà utilisée.'],
                'old' => $data,
                'roles' => Role::getAll()
            ]);
            return;
        }

        if (!$createdUserId) {
            Session::set('error', 'Impossible de créer cet utilisateur.');
            $this->redirect(url('users/create'));
            return;
        }

        Session::set('success', 'Utilisateur créé avec succès.');

        $this->redirect(url('users'));
    }

    /**
     * Afficher les informations d'un utilisateur.
     */
    public function show(string $id): void
    {
        if (!$this->ensureAdmin()) {
            return;
        }

        $user = User::findById((int) $id);

        if (!$user) {
            $this->renderError(404);
            return;
        }

        $this->view('admin.users.show', [
            'user' => $user
        ]);
    }

    /**
     * Afficher le formulaire de modification.
     */
    public function edit(string $id): void
    {
        if (!$this->ensureAdmin()) {
            return;
        }

        $user = User::findById((int) $id);

        if (!$user) {
            $this->renderError(404);
            return;
        }

        $this->view('admin.users.edit', [
            'user' => $user,
            'roles' => Role::getAll(),
            'companies' => Company::getAll(),
            'assignedCompanyIds' => UserCompany::getCompanyIdsByUser((int) $id)
        ]);
    }

    /**
     * Mettre à jour un utilisateur.
     */
    public function update(string $id): void
    {
        if (!$this->ensureAdmin()) {
            return;
        }

        $user = User::findById((int) $id);

        if (!$user) {
            $this->renderError(404);
            return;
        }

        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'role_id' => $_POST['role_id'] ?? '',
            'status' => $_POST['status'] ?? USER_ACTIVE
        ];
        $submittedCompanyIds = $_POST['company_ids'] ?? [];
        $companies = Company::getAll();
        $validCompanyIds = array_map('intval', array_column($companies, 'id'));
        $companyIds = [];
        $invalidCompanyIds = !is_array($submittedCompanyIds);
        if (is_array($submittedCompanyIds)) {
            foreach ($submittedCompanyIds as $companyId) {
                $validatedId = filter_var($companyId, FILTER_VALIDATE_INT);
                if ($validatedId === false || $validatedId <= 0 || !in_array($validatedId, $validCompanyIds, true)) {
                    $invalidCompanyIds = true;
                    break;
                }
                $companyIds[] = $validatedId;
            }
        }

        if (
            (int) $id === Auth::id() &&
            (
                (int) $data['role_id'] !== (int) $user['role_id'] ||
                $data['status'] !== $user['status']
            )
        ) {
            Session::set('error', 'Vous ne pouvez pas modifier votre propre rôle ou statut.');
            $this->redirect(url('users/' . $id . '/edit'));
            return;
        }

        $validation = UserValidator::validateUpdate($data, (int) $id);

        if (!$validation['valid'] || $invalidCompanyIds) {
            $errors = array_values($validation['errors']);
            if ($invalidCompanyIds) {
                $errors[] = 'La sélection des entreprises est invalide.';
            }
            $this->view('admin.users.edit', [
                'user' => array_merge($user, $data),
                'errors' => $errors,
                'roles' => Role::getAll(),
                'companies' => $companies,
                'assignedCompanyIds' => $companyIds
            ]);
            return;
        }

        $data['role_id'] = (int) $data['role_id'];

        if (!User::update((int) $id, $data)) {
            Session::set('error', 'Impossible de modifier cet utilisateur.');
            $this->redirect(url('users/' . $id . '/edit'));
            return;
        }

        if (!UserCompany::syncForUser((int) $id, $companyIds)) {
            Session::set('error', 'Les entreprises autorisées n’ont pas pu être mises à jour.');
            $this->redirect(url('users/' . $id . '/edit'));
            return;
        }

        Session::set('success', 'Utilisateur modifié avec succès.');

        $this->redirect(url('users'));
    }

    /**
     * Modifier le statut d'un utilisateur.
     */
    public function updateStatus(string $id): void
    {
        if (!$this->ensureAdmin()) {
            return;
        }

        $user = User::findById((int) $id);

        if (!$user) {
            $this->renderError(404);
            return;
        }

        if ((int) $id === Auth::id()) {
            Session::set('error', 'Vous ne pouvez pas modifier le statut de votre propre compte.');
            $this->redirect(url('users'));
            return;
        }

        $status = $_POST['status'] ?? '';

        $validation = UserValidator::validateStatus($status);

        if (!$validation['valid']) {
            Session::set('error', 'Statut utilisateur invalide.');
            $this->redirect(url('users'));
            return;
        }

        if ((int) $id === Auth::id()) {
            Session::set('error', 'Tu ne peux pas modifier ton propre statut.');
            $this->redirect(url('users'));
            return;
        }

        if (!User::updateStatus((int) $id, $status)) {
            Session::set('error', 'Impossible de modifier le statut.');
            $this->redirect(url('users'));
            return;
        }

        Session::set('success', 'Statut utilisateur mis à jour.');

        $this->redirect(url('users'));
    }

    /**
     * Supprimer un utilisateur.
     */
    public function delete(string $id): void
    {
        if (!$this->ensureAdmin()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(url('users'));
            return;
        }

        if ((int) $id === Auth::id()) {
            Session::set('error', 'Tu ne peux pas supprimer ton propre compte.');
            $this->redirect(url('users'));
            return;
        }

        $user = User::findById((int) $id);

        if (!$user) {
            $this->renderError(404);
            return;
        }

        try {
            $deleted = User::delete((int) $id);
        } catch (\PDOException $exception) {
            if ((string) $exception->getCode() === '23000') {
                Session::set(
                    'error',
                    'Suppression impossible : cet utilisateur est encore associé à des documents ou à des données conservées. Désactivez ou suspendez plutôt son compte.'
                );
                $this->redirect(url('users'));
                return;
            }

            error_log('Erreur suppression utilisateur : ' . $exception->getMessage());
            Session::set('error', 'Une erreur est survenue pendant la suppression. Consultez le journal serveur.');
            $this->redirect(url('users'));
            return;
        }

        if (!$deleted) {
            Session::set('error', 'Impossible de supprimer cet utilisateur.');
            $this->redirect(url('users'));
            return;
        }

        Session::set('success', 'Utilisateur supprimé avec succès.');

        $this->redirect(url('users'));
    }

    private function ensureAdmin(): bool
    {
        if (Auth::hasRole(ROLE_ADMIN)) {
            return true;
        }

        if (!Auth::check()) {
            $this->redirect(url('login'));
            return false;
        }

        $this->renderError(403);
        return false;
    }
}