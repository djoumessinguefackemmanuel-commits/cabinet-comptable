<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\ReportQuery;

class LedgerController extends Controller
{
    /**
     * Afficher le grand livre comptable (filtres : compte, période).
     */
    public function index(): void
    {
        $query = new ReportQuery();

        $accountId = (int) ($_GET['account_id'] ?? 0);
        $start = ReportQuery::date($_GET['start_date'] ?? null);
        $end = ReportQuery::date($_GET['end_date'] ?? null);

        $selectedAccount = $accountId > 0 ? $query->account($accountId) : null;

        // Un compte demandé mais inaccessible ne doit révéler aucune donnée.
        $entries = ($accountId > 0 && $selectedAccount === null)
            ? []
            : $query->ledgerEntries($selectedAccount ? $accountId : null, $start, $end);

        $this->view('accounting.ledger.index', [
            'title' => 'Grand livre comptable',
            'entries' => $entries,
            'accounts' => $query->accounts(),
            'selectedAccount' => $selectedAccount
        ]);
    }
}
