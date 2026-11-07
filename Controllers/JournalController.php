<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Company;
use App\Models\Journal;

class JournalController extends Controller
{
    /**
     * Afficher la liste des journaux comptables.
     */
    public function index(): void
    {
        $companyIds = \App\Core\Auth::companyIds();
        $journals = $companyIds === null
            ? Journal::getAll()
            : Journal::getByCompanies($companyIds);

        $this->view('accounting.journals.index', [
            'journals' => $journals
        ]);
    }

    /**
     * Afficher le formulaire de création d'un journal.
     */
    public function create(): void
    {
        $this->view('accounting.journals.create', [
            'companies' => Company::getAll()
        ]);
    }

    /**
     * Enregistrer un nouveau journal comptable.
     */
    public function store(): void
    {
        $data = [
            'company_id' => (int) ($_POST['company_id'] ?? 0),
            'code' => strtoupper(trim($_POST['code'] ?? '')),
            'name' => trim($_POST['name'] ?? ''),
            'type' => trim($_POST['type'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        if (
            $data['company_id'] <= 0 ||
            $data['code'] === '' ||
            $data['name'] === '' ||
            $data['type'] === ''
        ) {
            Session::set('error', 'Veuillez remplir tous les champs obligatoires.');
            $this->redirect(url('accounting/journals/create'));
            return;
        }

        $allowedTypes = [
            'sales',
            'purchases',
            'cash',
            'bank',
            'miscellaneous'
        ];

        if (!in_array($data['type'], $allowedTypes, true)) {
            Session::set('error', 'Le type de journal sélectionné est invalide.');
            $this->redirect(url('accounting/journals/create'));
            return;
        }

        if (Journal::create($data)) {
            Session::set('success', 'Journal comptable créé avec succès.');
            $this->redirect(url('accounting/journals'));
            return;
        }

        Session::set('error', 'Impossible de créer ce journal comptable.');
        $this->redirect(url('accounting/journals/create'));
    }
}