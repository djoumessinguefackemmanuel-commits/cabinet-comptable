<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Company;
use App\Models\Exercise;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    /**
     * Afficher la liste des factures.
     */
    public function index(): void
    {
        $filters = [
            'company_id' => max(0, (int) ($_GET['company_id'] ?? 0)),
            'status' => trim($_GET['status'] ?? ''),
            'start_date' => trim($_GET['start_date'] ?? ''),
            'end_date' => trim($_GET['end_date'] ?? '')
        ];

        if (!in_array(
            $filters['status'],
            ['', INVOICE_DRAFT, INVOICE_PENDING, INVOICE_PAID, INVOICE_CANCELLED],
            true
        )) {
            $filters['status'] = '';
        }

        $companyIds = Auth::companyIds();
        if ($companyIds !== null) {
            $filters['company_ids'] = $companyIds;
        }

        $invoices = Invoice::getAll($filters);
        $this->view('invoices.index', [
            'invoices' => $invoices,
            'filters' => $filters,
            'companies' => $companyIds === null
                ? Company::getAll()
                : Company::getByUser(Auth::id() ?? 0),
            'exercises' => $companyIds === null
                ? Exercise::getAll()
                : Exercise::getByCompanies($companyIds)
        ]);
    }

    /**
     * Créer une facture depuis le formulaire de la liste.
     */
    public function store(): void
    {
        $data = [
            'company_id' => (int) ($_POST['company_id'] ?? 0),
            'exercise_id' => (int) ($_POST['exercise_id'] ?? 0),
            'invoice_number' => trim($_POST['invoice_number'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'amount' => trim($_POST['amount'] ?? ''),
            'invoice_date' => trim($_POST['invoice_date'] ?? ''),
            'due_date' => trim($_POST['due_date'] ?? '')
        ];

        if (
            $data['company_id'] <= 0
            || $data['invoice_number'] === ''
            || !is_numeric($data['amount'])
            || (float) $data['amount'] < 0
            || !$this->isValidDate($data['invoice_date'])
            || ($data['due_date'] !== '' && !$this->isValidDate($data['due_date']))
        ) {
            Session::set('error', 'Vérifiez les informations obligatoires de la facture.');
            $this->redirect(url('invoices'));
            return;
        }

        $data['exercise_id'] = $data['exercise_id'] > 0 ? $data['exercise_id'] : null;
        $data['user_id'] = Auth::id();
        $data['amount'] = (float) $data['amount'];
        $data['due_date'] = $data['due_date'] !== '' ? $data['due_date'] : null;

        try {
            if (!Invoice::create($data)) {
                Session::set('error', 'La facture n’a pas pu être enregistrée.');
                $this->redirect(url('invoices'));
                return;
            }
        } catch (\PDOException $exception) {
            error_log('Erreur de création de facture : ' . $exception->getMessage());
            Session::set('error', 'La facture n’a pas pu être enregistrée. Vérifiez que son numéro est unique pour cette entreprise.');
            $this->redirect(url('invoices'));
            return;
        }

        Session::set('success', 'Facture créée avec succès.');
        $this->redirect(url('invoices'));
    }

    public function updateStatus(string $id): void
    {
        $invoiceId = (int) $id;
        if ($invoiceId <= 0 || !Invoice::getById($invoiceId)) {
            $this->renderError(404);
            return;
        }

        $status = trim($_POST['status'] ?? '');
        if (!Invoice::updateStatus($invoiceId, $status)) {
            Session::set('error', 'Impossible de modifier le statut de cette facture.');
            $this->redirect(url('invoices'));
            return;
        }

        Session::set('success', 'Statut de la facture mis à jour.');
        $this->redirect(url('invoices'));
    }

    public function delete(string $id): void
    {
        $invoiceId = (int) $id;
        if ($invoiceId <= 0 || !Invoice::getById($invoiceId)) {
            $this->renderError(404);
            return;
        }

        if (!Invoice::delete($invoiceId)) {
            Session::set('error', 'Impossible de supprimer cette facture.');
            $this->redirect(url('invoices'));
            return;
        }

        Session::set('success', 'Facture supprimée.');
        $this->redirect(url('invoices'));
    }

    private function isValidDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value;
    }
}