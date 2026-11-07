<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Exercise;
use App\Services\FinancialStatementService;

class FinancialStatementController extends Controller
{
    /**
     * Afficher le bilan comptable.
     */
    public function balanceSheet(): void
    {
        $exerciseId = (int) ($_GET['exercise_id'] ?? 0);

        $exercises = $this->accessibleExercises();
        $statement = [];

        if ($this->canAccessExercise($exerciseId, $exercises)) {
            $service = new FinancialStatementService();
            $statement = $service->getBalanceSheet($exerciseId);
        }

        $this->view('reports.balance-sheet', [
            'statement' => $statement,
            'exercises' => $exercises,
            'selectedExercise' => $exerciseId
        ]);
    }

    /**
     * Afficher le compte de résultat.
     */
    public function incomeStatement(): void
    {
        $exerciseId = (int) ($_GET['exercise_id'] ?? 0);

        $exercises = $this->accessibleExercises();
        $statement = [];

        if ($this->canAccessExercise($exerciseId, $exercises)) {
            $service = new FinancialStatementService();
            $statement = $service->getIncomeStatement($exerciseId);
        }

        $this->view('reports.income-statement', [
            'statement' => $statement,
            'exercises' => $exercises,
            'selectedExercise' => $exerciseId
        ]);
    }

    /**
     * Afficher la balance générale.
     */
    public function trialBalance(): void
    {
        $exerciseId = (int) ($_GET['exercise_id'] ?? 0);

        $exercises = $this->accessibleExercises();
        $statement = [];

        if ($this->canAccessExercise($exerciseId, $exercises)) {
            $service = new FinancialStatementService();
            $statement = $service->getTrialBalance($exerciseId);
        }

        $this->view('reports.trial-balance', [
            'statement' => $statement,
            'exercises' => $exercises,
            'selectedExercise' => $exerciseId
        ]);
    }

    /**
     * Afficher le grand livre général.
     */
    public function generalLedger(): void
    {
        $exerciseId = (int) ($_GET['exercise_id'] ?? 0);

        $exercises = $this->accessibleExercises();
        $statement = [];

        if ($this->canAccessExercise($exerciseId, $exercises)) {
            $service = new FinancialStatementService();
            $statement = $service->getGeneralLedger($exerciseId);
        }

        $this->view('reports.general-ledger', [
            'statement' => $statement,
            'exercises' => $exercises,
            'selectedExercise' => $exerciseId
        ]);
    }

    private function accessibleExercises(): array
    {
        $companyIds = \App\Core\Auth::companyIds();
        return $companyIds === null
            ? Exercise::getAll()
            : Exercise::getByCompanies($companyIds);
    }

    private function canAccessExercise(int $exerciseId, array $exercises): bool
    {
        foreach ($exercises as $exercise) {
            if ((int) $exercise['id'] === $exerciseId) {
                return true;
            }
        }

        return false;
    }
}