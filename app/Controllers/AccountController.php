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

        $companyList = Company::getAll();
        if ($companyIds !== null) {
            $companyList = array_values(array_filter($companyList, static fn ($c) => in_array((int) $c['id'], $companyIds, true)));
        }

        $this->view('accounting.accounts.index', [
            'accounts' => $accounts,
            'companies' => $companyList,
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

    /**
     * Importer le plan comptable SYSCOHADA dans une entreprise.
     */
    public function importSyscohada(): void
    {
        $companyId = (int) ($_POST['company_id'] ?? 0);
        $companyIds = \App\Core\Auth::companyIds();

        if ($companyId <= 0 || Company::findById($companyId) === null
            || ($companyIds !== null && !in_array($companyId, $companyIds, true))) {
            Session::set('error', 'Veuillez choisir une entreprise valide.');
            $this->redirect(url('accounting/accounts'));
            return;
        }

        $chart = require BASE_PATH . '/database/syscohada.php';

        try {
            $added = Account::importSyscohada($companyId, $chart);
            Session::set('success', $added > 0
                ? "Plan comptable SYSCOHADA importé : {$added} compte(s) ajouté(s)."
                : 'Le plan SYSCOHADA est déjà présent dans cette entreprise.');
        } catch (\Throwable $e) {
            error_log('Import SYSCOHADA : ' . $e->getMessage());
            Session::set('error', "L'import du plan comptable a échoué.");
        }

        $this->redirect(url('accounting/accounts'));
    }
}