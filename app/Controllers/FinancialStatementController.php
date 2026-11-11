<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Exercise;
use App\Services\FinancialStatementService;
use App\Services\ReportQuery;

class FinancialStatementController extends Controller
{
    /**
     * Afficher le bilan comptable à une date (filtre : date).
     */
    public function balanceSheet(): void
    {
        $date = ReportQuery::date($_GET['date'] ?? null) ?? date('Y-m-d');

        $this->view('reports.balance-sheet', [
            'title' => 'Bilan comptable',
            'report' => (new ReportQuery())->balanceSheet($date)
        ]);
    }

    /**
     * Afficher le compte de résultat (filtre : période).
     */
    public function incomeStatement(): void
    {
        $this->view('reports.income-statement', [
            'title' => 'Compte de résultat',
            'report' => (new ReportQuery())->incomeStatement(
                ReportQuery::date($_GET['start_date'] ?? null),
                ReportQuery::date($_GET['end_date'] ?? null)
            )
        ]);
    }

    /**
     * Afficher la balance générale (filtre : période).
     */
    public function trialBalance(): void
    {
        $start = ReportQuery::date($_GET['start_date'] ?? null);
        $end = ReportQuery::date($_GET['end_date'] ?? null);

        $report = (new ReportQuery())->trialBalance($start, $end);
        $report['start_date'] = $start ?? '';
        $report['end_date'] = $end ?? '';

        $this->view('reports.trial-balance', [
            'title' => 'Balance générale',
            'report' => $report,
            'accounts' => $report['accounts']
        ]);
    }

    /**
     * Afficher le grand livre général d'un compte (filtres : compte, période).
     */
    public function generalLedger(): void
    {
        $query = new ReportQuery();

        $accountId = (int) ($_GET['account_id'] ?? 0);
        $start = ReportQuery::date($_GET['start_date'] ?? null);
        $end = ReportQuery::date($_GET['end_date'] ?? null);

        $selectedAccount = $accountId > 0 ? $query->account($accountId) : null;
        $report = [
            'entries' => [],
            'total_debit' => 0,
            'total_credit' => 0,
            'final_balance' => 0
        ];

        if ($selectedAccount !== null) {
            $report = $query->accountLedger($accountId, $start, $end);
        }

        $report['start_date'] = $start ?? '';
        $report['end_date'] = $end ?? '';

        $this->view('reports.general-ledger', [
            'title' => 'Grand livre général',
            'report' => $report,
            'entries' => $report['entries'],
            'accounts' => $query->accounts(),
            'selectedAccount' => $selectedAccount
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