<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Exercise;
use App\Models\Company;
use App\Validators\ExerciseValidator;

class ExerciseController extends Controller
{
    /**
     * Afficher la liste des exercices comptables.
     */
    public function index(): void
    {
        $companyIds = \App\Core\Auth::companyIds();
        $exercises = $companyIds === null
            ? Exercise::getAll()
            : Exercise::getByCompanies($companyIds);

        $this->view('exercises.index', [
            'exercises' => $exercises
        ]);
    }

    /**
     * Afficher le formulaire de création.
     */
    public function create(): void
    {
        $companies = Company::getAll();

        $this->view('exercises.create', [
            'companies' => $companies
        ]);
    }

    /**
     * Enregistrer un nouvel exercice comptable.
     */
    public function store(): void
    {
        $data = [
            'company_id' => (int) ($_POST['company_id'] ?? 0),
            'name' => trim($_POST['name'] ?? ''),
            'start_date' => trim($_POST['start_date'] ?? ''),
            'end_date' => trim($_POST['end_date'] ?? ''),
            'status' => EXERCISE_OPEN
        ];

        $validation = ExerciseValidator::validateCreate($data);

        if (!$validation['valid']) {
            $this->view('exercises.create', [
                'companies' => Company::getAll(),
                'errors' => $validation['errors'],
                'old' => $data
            ]);
            return;
        }

        if (!Exercise::create($data)) {
            Session::set('error', 'Impossible de créer cet exercice comptable.');
            $this->redirect(url('exercises/create'));
            return;
        }

        Session::set('success', 'Exercice comptable créé avec succès.');

        $this->redirect(url('exercises'));
    }

    /**
     * Afficher le formulaire de modification.
     */
    public function edit(string $id): void
    {
        $exercise = Exercise::findById((int) $id);

        if (!$exercise) {
            $this->renderError(404);
            return;
        }

        $companies = Company::getAll();

        $this->view('exercises.edit', [
            'exercise' => $exercise,
            'companies' => $companies
        ]);
    }

    /**
     * Modifier un exercice comptable.
     */
    public function update(string $id): void
    {
        $exercise = Exercise::findById((int) $id);

        if (!$exercise) {
            $this->renderError(404);
            return;
        }

        $data = [
            'company_id' => (int) ($_POST['company_id'] ?? 0),
            'name' => trim($_POST['name'] ?? ''),
            'start_date' => trim($_POST['start_date'] ?? ''),
            'end_date' => trim($_POST['end_date'] ?? '')
        ];

        $validation = ExerciseValidator::validateUpdate($data, (int) $id);

        if (!$validation['valid']) {
            $this->view('exercises.edit', [
                'exercise' => array_merge($exercise, $data),
                'companies' => Company::getAll(),
                'errors' => $validation['errors']
            ]);
            return;
        }

        if (!Exercise::update((int) $id, $data)) {
            Session::set('error', 'Impossible de modifier cet exercice comptable.');
            $this->redirect(url('exercises/' . $id . '/edit'));
            return;
        }

        Session::set('success', 'Exercice comptable modifié avec succès.');

        $this->redirect(url('exercises'));
    }

    /**
     * Modifier le statut d'un exercice comptable.
     */
    public function updateStatus(string $id): void
    {
        $exercise = Exercise::findById((int) $id);

        if (!$exercise) {
            $this->renderError(404);
            return;
        }

        $status = trim($_POST['status'] ?? '');

        $validation = ExerciseValidator::validateStatus($status);

        if (!$validation['valid']) {
            Session::set('error', 'Le statut sélectionné est invalide.');
            $this->redirect(url('exercises'));
            return;
        }

        if (!Exercise::updateStatus((int) $id, $status)) {
            Session::set('error', 'Impossible de modifier le statut de cet exercice.');
            $this->redirect(url('exercises'));
            return;
        }

        Session::set('success', 'Statut de l’exercice modifié avec succès.');

        $this->redirect(url('exercises'));
    }

    public function delete(string $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(url('exercises'));
            return;
        }

        if (!Exercise::findById((int) $id)) {
            $this->renderError(404);
            return;
        }

        try {
            if (!Exercise::delete((int) $id)) {
                Session::set('error', 'Impossible de supprimer cet exercice comptable.');
                $this->redirect(url('exercises'));
                return;
            }
        } catch (\PDOException $exception) {
            error_log('Suppression d’exercice comptable impossible : ' . $exception->getMessage());
            Session::set(
                'error',
                'Cet exercice ne peut pas être supprimé car il est utilisé par des données comptables.'
            );
            $this->redirect(url('exercises'));
            return;
        }

        Session::set('success', 'Exercice comptable supprimé.');
        $this->redirect(url('exercises'));
    }
}