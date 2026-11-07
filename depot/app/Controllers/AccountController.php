<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Account;
use App\Models\Company;

class AccountController extends Controller
{
    /**
     * Afficher la liste des comptes comptables.
     */
    public function index(): void
    {
        $companyIds = \App\Core\Auth::companyIds();
        $accounts = $companyIds === null
            ? Account::getAll()
            : Account::getByCompanies($companyIds);

        $this->view('accounting.accounts.index', [
            'accounts' => $accounts
        ]);
    }

    /**
     * Afficher le formulaire de création d'un compte.
     */
    public function create(): void
    {
        $this->view('accounting.accounts.create', [
            'companies' => Company::getAll()
        ]);
    }

    /**
     * Enregistrer un nouveau compte comptable.
     */
    public function store(): void
    {
        $data = [
            'company_id' => (int) ($_POST['company_id'] ?? 0),
            'account_number' => trim($_POST['account_number'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'type' => trim($_POST['type'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        $requiredFields = [
            'company_id',
            'account_number',
            'name',
            'type'
        ];

        foreach ($requiredFields as $field) {
            if ($data[$field] === '') {
                Session::set('error', 'Veuillez remplir tous les champs obligatoires.');
                $this->redirect(url('accounting/accounts/create'));
                return;
            }
        }

        $allowedTypes = [
            ACCOUNT_ASSET,
            ACCOUNT_LIABILITY,
            ACCOUNT_EQUITY,
            ACCOUNT_EXPENSE,
            ACCOUNT_REVENUE
        ];

        if (!in_array($data['type'], $allowedTypes, true)) {
            Session::set('error', 'Le type de compte sélectionné est invalide.');
            $this->redirect(url('accounting/accounts/create'));
            return;
        }

        if (Account::create($data)) {
            Session::set('success', 'Compte comptable créé avec succès.');
            $this->redirect(url('accounting/accounts'));
            return;
        }

        Session::set('error', 'Impossible de créer ce compte comptable.');
        $this->redirect(url('accounting/accounts/create'));
    }
}