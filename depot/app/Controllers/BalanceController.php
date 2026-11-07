<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Balance;
use App\Models\Exercise;

class BalanceController extends Controller
{
    /**
     * Afficher la balance comptable.
     */
    public function index(): void
    {
        $exerciseId = (int) ($_GET['exercise_id'] ?? 0);

        $companyIds = \App\Core\Auth::companyIds();
        $exercises = $companyIds === null
            ? Exercise::getAll()
            : Exercise::getByCompanies($companyIds);
        $balance = [];
        $totals = [
            'total_debit' => 0,
            'total_credit' => 0,
            'total_debit_balance' => 0,
            'total_credit_balance' => 0
        ];

        $canAccessExercise = false;
        foreach ($exercises as $exercise) {
            if ((int) $exercise['id'] === $exerciseId) {
                $canAccessExercise = true;
                break;
            }
        }

        if ($canAccessExercise) {
            $balance = Balance::getByExercise($exerciseId);
            $totals = Balance::getTotals($exerciseId);
        }

        $this->view('accounting.balance.index', [
            'balance' => $balance,
            'totals' => $totals,
            'exercises' => $exercises,
            'selectedExercise' => $exerciseId
        ]);
    }
}