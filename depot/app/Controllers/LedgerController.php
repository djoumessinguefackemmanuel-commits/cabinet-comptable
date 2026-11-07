<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Ledger;
use App\Models\Account;
use App\Models\Exercise;

class LedgerController extends Controller
{
    /**
     * Afficher le grand livre comptable.
     */
    public function index(): void
    {
        $exerciseId = (int) ($_GET['exercise_id'] ?? 0);
        $accountId = (int) ($_GET['account_id'] ?? 0);

        $companyIds = \App\Core\Auth::companyIds();
        $exercises = $companyIds === null
            ? Exercise::getAll()
            : Exercise::getByCompanies($companyIds);
        $accounts = $companyIds === null
            ? Account::getAll()
            : Account::getByCompanies($companyIds);

        $ledger = [];

        $canAccessExercise = false;
        foreach ($exercises as $exercise) {
            if ((int) $exercise['id'] === $exerciseId) {
                $canAccessExercise = true;
                break;
            }
        }

        if ($canAccessExercise) {
            $ledger = Ledger::getByExercise(
                $exerciseId,
                $accountId > 0 ? $accountId : null
            );
        }

        $this->view('accounting.ledger.index', [
            'ledger' => $ledger,
            'exercises' => $exercises,
            'accounts' => $accounts,
            'selectedExercise' => $exerciseId,
            'selectedAccount' => $accountId
        ]);
    }
}