<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Company;
use App\Models\Document;
use App\Models\Exercise;
use App\Services\DocumentService;

class DocumentController extends Controller
{
    /**
     * Afficher la liste des documents.
     */
    public function index(): void
    {
        $companyId = (int) ($_GET['company_id'] ?? 0);
        $companyIds = Auth::companyIds();

        if ($companyIds === null && $companyId > 0) {
            $documents = Document::getByCompany($companyId);
        } elseif ($companyIds === null) {
            $documents = Document::getAll();
        } elseif ($companyId > 0) {
            $documents = in_array($companyId, $companyIds, true)
                ? Document::getByCompany($companyId)
                : [];
        } else {
            $documents = [];
            foreach ($companyIds as $accessibleCompanyId) {
                $documents = array_merge(
                    $documents,
                    Document::getByCompany((int) $accessibleCompanyId)
                );
            }
        }

        $this->view('documents.index', [
            'documents' => $documents,
            'selectedCompany' => $companyId,
            'companies' => $companyIds === null
                ? Company::getAll()
                : Company::getByUser(Auth::id() ?? 0),
            'exercises' => $companyIds === null
                ? Exercise::getAll()
                : Exercise::getByCompanies($companyIds)
        ]);
    }

    /**
     * Téléverser un document.
     */
    public function upload(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(url('documents'));
            return;
        }

        if (
            !isset($_FILES['document']) ||
            $_FILES['document']['error'] !== UPLOAD_ERR_OK
        ) {
            Session::set('error', 'Veuillez sélectionner un document valide.');
            $this->redirect(url('documents'));
            return;
        }

        $companyId = (int) ($_POST['company_id'] ?? 0);
        $exerciseId = (int) ($_POST['exercise_id'] ?? 0);
        $companyIds = Auth::companyIds();
        if (
            $companyId <= 0
            || ($companyIds !== null && !in_array($companyId, $companyIds, true))
        ) {
            Session::set('error', 'Sélectionnez une entreprise pour ce document.');
            $this->redirect(url('documents'));
            return;
        }

        try {
            (new DocumentService())->upload(
                $_FILES['document'],
                Auth::id() ?? 0,
                $companyId,
                $exerciseId > 0 ? $exerciseId : null
            );
            Session::set('success', 'Document téléversé avec succès.');
        } catch (\RuntimeException $exception) {
            Session::set('error', $exception->getMessage());
        }

        $this->redirect(url('documents'));
    }

    /**
     * Télécharger un document.
     */
    public function download(string $id): void
    {
        $document = Document::findById((int) $id);

        if (
            !$document
            || (
                Auth::companyIds() !== null
                && !in_array(
                    (int) $document['company_id'],
                    Auth::companyIds(),
                    true
                )
            )
        ) {
            $this->renderError(404);
            return;
        }

        $filePath = STORAGE_PATH . '/documents/' . basename($document['file_name']);

        if (!is_file($filePath)) {
            $this->renderError(404);
            return;
        }

        header('Content-Type: application/octet-stream');
        header('X-Content-Type-Options: nosniff');
        header(
            'Content-Disposition: attachment; filename="' .
            basename($document['file_name']) . '"'
        );
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
        exit;
    }

    /**
     * Supprimer un document.
     */
    public function delete(string $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(url('documents'));
            return;
        }

        $document = Document::findById((int) $id);

        if (
            !$document
            || (
                !in_array(
                    (Auth::user()['role'] ?? ''),
                    [ROLE_ADMIN, ROLE_EXPERT],
                    true
                )
                && (
                    (int) $document['user_id'] !== (int) Auth::id()
                    || !in_array(
                        (int) $document['company_id'],
                        Auth::companyIds() ?? [],
                        true
                    )
                )
            )
        ) {
            $this->renderError(404);
            return;
        }

        $service = new DocumentService();

        if (!$service->delete((int) $id, (int) $document['company_id'])) {
            Session::set('error', 'Impossible de supprimer ce document.');
            $this->redirect(url('documents'));
            return;
        }

        Session::set('success', 'Document supprimé avec succès.');

        $this->redirect(url('documents'));
    }
}