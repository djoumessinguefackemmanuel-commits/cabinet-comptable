<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Tax;
use App\Models\Exercise;
use App\Services\TaxCalculationService;

class TaxController extends Controller
{
    public function calculate(): void
    {
        $amount = $_POST['amount'] ?? null;
        $rate = $_POST['tax_rate'] ?? null;

        if (
            !is_numeric($amount)
            || !is_numeric($rate)
            || !is_finite((float) $amount)
            || !is_finite((float) $rate)
            || (float) $amount < 0
            || (float) $rate < 0
        ) {
            $this->json(['error' => 'Le montant et le taux doivent être des nombres positifs.'], 422);
            return;
        }

        $result = (new TaxCalculationService())->calculateTotalWithTax(
            (float) $amount,
            (float) $rate
        );
        $this->json($result);
    }

    /**
     * Afficher le tableau de bord fiscal.
     */
    public function index(): void
    {
        $exerciseId = (int) ($_GET['exercise_id'] ?? 0);

        $exercises = $this->accessibleExercises();
        $taxes = [];

        if ($this->canAccessExercise($exerciseId, $exercises)) {
            $taxes = Tax::getByExercise($exerciseId);
        }

        $this->view('taxes.index', [
            'taxes' => $taxes,
            'exercises' => $exercises,
            'selectedExercise' => $exerciseId
        ]);
    }

    /**
     * Afficher les informations relatives à la TVA.
     */
    public function vat(): void
    {
        $exerciseId = (int) ($_GET['exercise_id'] ?? 0);
        $exercises = $this->accessibleExercises();
        $vatData = [];
        $errors = [];

        if ($this->canAccessExercise($exerciseId, $exercises)) {
            try {
                $service = new TaxCalculationService();
                $vatData = $service->calculateVAT(
                    $exerciseId,
                    trim($_GET['start_date'] ?? '') ?: null,
                    trim($_GET['end_date'] ?? '') ?: null
                );
            } catch (\InvalidArgumentException $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        $this->view('taxes.vat', [
            'vatData' => $vatData,
            'exercises' => $exercises,
            'selectedExercise' => $exerciseId,
            'errors' => $errors
        ]);
    }

    /**
     * Afficher les déclarations fiscales.
     */
    public function declarations(): void
    {
        $exerciseId = (int) ($_GET['exercise_id'] ?? 0);

        $exercises = $this->accessibleExercises();
        $declarations = [];

        if ($this->canAccessExercise($exerciseId, $exercises)) {
            $declarations = Tax::getDeclarations($exerciseId);
        }

        $this->view('taxes.declarations', [
            'declarations' => $declarations,
            'exercises' => $exercises,
            'selectedExercise' => $exerciseId
        ]);
    }

    /**
     * Afficher les échéances fiscales.
     */
    public function deadlines(): void
    {
        $exerciseId = (int) ($_GET['exercise_id'] ?? 0);
        $exercises = $this->accessibleExercises();
        $deadlines = $this->canAccessExercise($exerciseId, $exercises)
            ? Tax::getDeadlines($exerciseId)
            : [];

        $this->view('taxes.deadlines', [
            'deadlines' => $deadlines,
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