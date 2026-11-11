<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\ReportQuery;

class BalanceController extends Controller
{
    /**
     * Afficher la balance comptable (filtre : période).
     */
    public function index(): void
    {
        $report = (new ReportQuery())->trialBalance(
            ReportQuery::date($_GET['start_date'] ?? null),
            ReportQuery::date($_GET['end_date'] ?? null)
        );

        $this->view('accounting.balance.index', [
            'title' => 'Balance comptable',
            'balances' => $report['accounts']
        ]);
    }
}
