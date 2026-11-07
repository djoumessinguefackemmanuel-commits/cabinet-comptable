<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Journal;
use App\Models\Exercise;

class ReconciliationController extends Controller
{
    /**
     * Afficher les opérations disponibles pour le rapprochement.
     */
    public function index(): void
    {
        $companyIds = \App\Core\Auth::companyIds();
        $exercises = $companyIds === null
            ? Exercise::getAll()
            : Exercise::getByCompanies($companyIds);
        $journals = $companyIds === null
            ? Journal::getAll()
            : Journal::getByCompanies($companyIds);

        $this->view('accounting.reconciliation.index', [
            'exercises' => $exercises,
            'journals' => $journals
        ]);
    }

    /**
     * Effectuer le rapprochement d'une opération.
     */
    public function reconcile(string $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(url('accounting/reconciliation'));
            return;
        }

        Session::set('error', 'Le rapprochement n’est pas encore configuré : aucune source bancaire n’est disponible.');
        $this->redirect(url('accounting/reconciliation'));
    }

    /**
     * Annuler le rapprochement d'une opération.
     */
    public function unreconcile(string $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(url('accounting/reconciliation'));
            return;
        }

        Session::set('error', 'Le rapprochement n’est pas encore configuré : aucune source bancaire n’est disponible.');
        $this->redirect(url('accounting/reconciliation'));
    }
}