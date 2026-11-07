<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Entry;
use App\Models\Journal;
use App\Models\Account;
use App\Models\Exercise;
use App\Services\AccountingService;
use App\Validators\EntryValidator;

class EntryController extends Controller
{
    /**
     * Afficher la liste des écritures comptables.
     */
    public function index(): void
    {
        $companyIds = Auth::companyIds();
        $entries = $companyIds === null
            ? Entry::getAll()
            : Entry::getByCompanies($companyIds);

        $this->view('accounting.entries.index', [
            'entries' => $entries
        ]);
    }

    /**
     * Afficher le formulaire de création.
     */
    public function create(): void
    {
        $companyIds = Auth::companyIds();
        $journals = $companyIds === null
            ? Journal::getAllActive()
            : Journal::getByCompanies($companyIds, true);
        $accounts = $companyIds === null
            ? Account::getAllActive()
            : Account::getByCompanies($companyIds, true);
        $exercises = $companyIds === null
            ? Exercise::getAllOpen()
            : Exercise::getByCompanies($companyIds, true);

        $this->view('accounting.entries.create', [
            'journals' => $journals,
            'accounts' => $accounts,
            'exercises' => $exercises
        ]);
    }

    /**
     * Enregistrer une nouvelle écriture comptable.
     */
    public function store(): void
    {
        $data = [
            'journal_id' => (int) ($_POST['journal_id'] ?? 0),
            'exercise_id' => (int) ($_POST['exercise_id'] ?? 0),
            'entry_date' => trim($_POST['entry_date'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'lines' => $_POST['lines'] ?? []
        ];

        $validation = EntryValidator::validateCreate($data);

        if (!$validation['valid']) {
            $companyIds = Auth::companyIds();
            $this->view('accounting.entries.create', [
                'journals' => $companyIds === null
                    ? Journal::getAllActive()
                    : Journal::getByCompanies($companyIds, true),
                'accounts' => $companyIds === null
                    ? Account::getAllActive()
                    : Account::getByCompanies($companyIds, true),
                'exercises' => $companyIds === null
                    ? Exercise::getAllOpen()
                    : Exercise::getByCompanies($companyIds, true),
                'errors' => $validation['errors'],
                'old' => $data
            ]);
            return;
        }

        $exercise = Exercise::findById($data['exercise_id']);
        $journal = Journal::findById($data['journal_id']);

        if (
            !$exercise ||
            $exercise['status'] !== EXERCISE_OPEN ||
            !$journal ||
            (int) $journal['is_active'] !== 1 ||
            (int) $journal['company_id'] !== (int) $exercise['company_id'] ||
            (
                $journal['exercise_id'] !== null &&
                (int) $journal['exercise_id'] !== (int) $exercise['id']
            )
        ) {
            Session::set('error', 'Le journal et l’exercice sélectionnés ne sont pas compatibles.');
            $this->redirect(url('accounting/entries/create'));
            return;
        }

        if (
            $data['entry_date'] < $exercise['start_date'] ||
            $data['entry_date'] > $exercise['end_date']
        ) {
            Session::set('error', 'La date de l’écriture doit être comprise dans la période de l’exercice.');
            $this->redirect(url('accounting/entries/create'));
            return;
        }

        foreach ($data['lines'] as $line) {
            $account = Account::findById((int) ($line['account_id'] ?? 0));

            if (
                !$account ||
                (int) $account['is_active'] !== 1 ||
                (int) $account['company_id'] !== (int) $exercise['company_id'] ||
                (
                    $account['exercise_id'] !== null &&
                    (int) $account['exercise_id'] !== (int) $exercise['id']
                )
            ) {
                Session::set('error', 'Chaque compte doit être actif et rattaché à l’entreprise et à l’exercice sélectionnés.');
                $this->redirect(url('accounting/entries/create'));
                return;
            }
        }

        $accountingService = new AccountingService();

        if (!$accountingService->isBalanced($data['lines'])) {
            Session::set('error', 'L’écriture comptable doit être équilibrée.');
            $this->redirect(url('accounting/entries/create'));
            return;
        }

        $data['company_id'] = (int) $exercise['company_id'];
        $data['user_id'] = Auth::id();
        $data['reference'] = Entry::generateReference();

        $entryId = Entry::createWithLines($data, $data['lines']);

        if (!$entryId) {
            Session::set('error', 'Impossible d’enregistrer cette écriture comptable.');
            $this->redirect(url('accounting/entries/create'));
            return;
        }

        Session::set('success', 'Écriture comptable enregistrée avec succès.');

        $this->redirect(url('accounting/entries/' . $entryId));
    }

    /**
     * Consulter une écriture comptable.
     */
    public function show(string $id): void
    {
        $entry = Entry::findWithLines((int) $id);

        if (!$entry) {
            $this->renderError(404);
            return;
        }

        $this->view('accounting.entries.show', [
            'entry' => $entry
        ]);
    }

    /**
     * Valider une écriture comptable.
     */
    public function validate(string $id): void
    {
        $entry = Entry::findById((int) $id);

        if (!$entry) {
            $this->renderError(404);
            return;
        }

        if ($entry['status'] !== ENTRY_DRAFT) {
            Session::set('error', 'Seules les écritures en brouillon peuvent être validées.');
            $this->redirect(url('accounting/entries/' . $id));
            return;
        }

        if (!Entry::updateStatus((int) $id, ENTRY_VALIDATED)) {
            Session::set('error', 'Impossible de valider cette écriture.');
            $this->redirect(url('accounting/entries/' . $id));
            return;
        }

        Session::set('success', 'Écriture comptable validée avec succès.');

        $this->redirect(url('accounting/entries/' . $id));
    }

    /**
     * Annuler une écriture comptable.
     */
    public function cancel(string $id): void
    {
        $entry = Entry::findById((int) $id);

        if (!$entry) {
            $this->renderError(404);
            return;
        }

        if ($entry['status'] === ENTRY_CANCELLED) {
            Session::set('warning', 'Cette écriture est déjà annulée.');
            $this->redirect(url('accounting/entries/' . $id));
            return;
        }

        if (!Entry::updateStatus((int) $id, ENTRY_CANCELLED)) {
            Session::set('error', 'Impossible d’annuler cette écriture.');
            $this->redirect(url('accounting/entries/' . $id));
            return;
        }

        Session::set('success', 'Écriture comptable annulée avec succès.');

        $this->redirect(url('accounting/entries/' . $id));
    }
}