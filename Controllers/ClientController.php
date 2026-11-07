<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\User;
use App\Models\Company;

class ClientController extends Controller
{
    /**
     * Afficher la liste des clients.
     */
    public function index(): void
    {
        $clients = User::getAllByRole(ROLE_CLIENT);

        $this->view('admin.users.index', [
            'users' => $clients
        ]);
    }

    /**
     * Afficher les informations d'un client.
     */
    public function show(string $id): void
    {
        $client = User::findById((int) $id);

        if (!$client || $client['role'] !== ROLE_CLIENT) {
            $this->renderError(404);
            return;
        }

        $companies = Company::getByUser((int) $id);

        $this->view('admin.clients.show', [
            'client' => $client,
            'companies' => $companies
        ]);
    }

    /**
     * Afficher les entreprises associées à un client.
     */
    public function companies(string $id): void
    {
        $client = User::findById((int) $id);

        if (!$client || $client['role'] !== ROLE_CLIENT) {
            $this->renderError(404);
            return;
        }

        $companies = Company::getByUser((int) $id);

        $this->view('admin.clients.companies', [
            'client' => $client,
            'companies' => $companies
        ]);
    }

    /**
     * Associer une entreprise à un client.
     */
    public function assignCompany(): void
    {
        $clientId = (int) ($_POST['user_id'] ?? 0);
        $companyId = (int) ($_POST['company_id'] ?? 0);

        if ($clientId <= 0 || $companyId <= 0) {
            Session::set('error', 'Veuillez sélectionner un client et une entreprise.');
            $this->redirect(url('clients'));
            return;
        }

        $client = User::findById($clientId);
        $company = Company::findById($companyId);

        if (!$client || $client['role'] !== ROLE_CLIENT || !$company) {
            Session::set('error', 'Le client ou l’entreprise sélectionné est introuvable.');
            $this->redirect(url('clients'));
            return;
        }

        $assigned = Company::assignUser($companyId, $clientId);

        if (!$assigned) {
            Session::set('error', 'Impossible d’associer cette entreprise au client.');
            $this->redirect(url('clients/' . $clientId));
            return;
        }

        Session::set('success', 'Entreprise associée au client avec succès.');

        $this->redirect(url('clients/' . $clientId));
    }

    /**
     * Retirer une entreprise d'un client.
     */
    public function removeCompany(): void
    {
        $clientId = (int) ($_POST['user_id'] ?? 0);
        $companyId = (int) ($_POST['company_id'] ?? 0);

        if ($clientId <= 0 || $companyId <= 0) {
            Session::set('error', 'Informations invalides.');
            $this->redirect(url('clients'));
            return;
        }

        $removed = Company::removeUser($companyId, $clientId);

        if (!$removed) {
            Session::set('error', 'Impossible de retirer cette association.');
            $this->redirect(url('clients/' . $clientId));
            return;
        }

        Session::set('success', 'Entreprise retirée du client avec succès.');

        $this->redirect(url('clients/' . $clientId));
    }
}