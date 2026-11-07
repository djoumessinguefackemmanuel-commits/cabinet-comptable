<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Company;
use App\Validators\CompanyValidator;

class CompanyController extends Controller
{
    /**
     * Afficher la liste des entreprises.
     */
    public function index(): void
    {
        $companies = Company::getAll();

        $this->view('companies.index', [
            'companies' => $companies
        ]);
    }

    /**
     * Afficher le formulaire de création.
     */
    public function create(): void
    {
        $this->view('companies.create');
    }

    /**
     * Enregistrer une nouvelle entreprise.
     */
    public function store(): void
    {
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'tax_number' => trim($_POST['tax_number'] ?? ''),
            'registration_number' => trim($_POST['registration_number'] ?? ''),
            'created_by' => Auth::id()
        ];

        $validation = CompanyValidator::validateCreate($data);

        if (!$validation['valid']) {
            $this->view('companies.create', [
                'errors' => $validation['errors'],
                'old' => $data
            ]);
            return;
        }

        if (!Company::create($data)) {
            Session::set('error', 'Impossible d’enregistrer cette entreprise.');
            $this->redirect(url('companies/create'));
            return;
        }

        Session::set('success', 'Entreprise créée avec succès.');

        $this->redirect(url('companies'));
    }

    /**
     * Afficher les détails d'une entreprise.
     */
    public function show(string $id): void
    {
        $company = Company::findById((int) $id);

        if (!$company) {
            $this->renderError(404);
            return;
        }

        $this->view('companies.show', [
            'company' => $company
        ]);
    }

    /**
     * Afficher le formulaire de modification.
     */
    public function edit(string $id): void
    {
        $company = Company::findById((int) $id);

        if (!$company) {
            $this->renderError(404);
            return;
        }

        $this->view('companies.edit', [
            'company' => $company
        ]);
    }

    /**
     * Modifier une entreprise.
     */
    public function update(string $id): void
    {
        $company = Company::findById((int) $id);

        if (!$company) {
            $this->renderError(404);
            return;
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'tax_number' => trim($_POST['tax_number'] ?? ''),
            'registration_number' => trim($_POST['registration_number'] ?? '')
        ];

        $validation = CompanyValidator::validateUpdate($data, (int) $id);

        if (!$validation['valid']) {
            $this->view('companies.edit', [
                'company' => array_merge($company, $data),
                'errors' => $validation['errors']
            ]);
            return;
        }

        if (!Company::update((int) $id, $data)) {
            Session::set('error', 'Impossible de modifier cette entreprise.');
            $this->redirect(url('companies/' . $id . '/edit'));
            return;
        }

        Session::set('success', 'Entreprise modifiée avec succès.');

        $this->redirect(url('companies'));
    }

    /**
     * Supprimer une entreprise.
     */
    public function delete(string $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(url('companies'));
            return;
        }

        $company = Company::findById((int) $id);

        if (!$company) {
            $this->renderError(404);
            return;
        }

        if (!Company::delete((int) $id)) {
            Session::set('error', 'Impossible de supprimer cette entreprise.');
            $this->redirect(url('companies'));
            return;
        }

        Session::set('success', 'Entreprise supprimée avec succès.');

        $this->redirect(url('companies'));
    }
}