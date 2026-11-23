<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\BankStatement;
use App\Services\BankStatementParser;

class ReconciliationController extends Controller
{
    private const WINDOW_DAYS = 7;

    public function index(): void
    {
        $accounts = BankStatement::bankAccounts(Auth::companyIds());
        $accountId = (int) ($_GET['account_id'] ?? 0);
        $current = null;
        foreach ($accounts as $a) {
            if ((int) $a['id'] === $accountId) {
                $current = $a;
            }
        }
        $current ??= $accounts[0] ?? null;

        $data = ['title' => 'Rapprochement bancaire', 'bankAccounts' => $accounts, 'current' => $current,
            'summary' => null, 'pending' => [], 'done' => [], 'openLines' => [], 'suggestions' => []];

        if ($current) {
            $cid = (int) $current['company_id'];
            $aid = (int) $current['id'];
            $data['summary'] = BankStatement::summary($cid, $aid);
            $data['pending'] = BankStatement::lines($cid, $aid, false);
            $data['done'] = BankStatement::lines($cid, $aid, true);
            $data['openLines'] = BankStatement::openEntryLines($cid, $aid);
            $data['suggestions'] = $this->suggest($data['pending'], $data['openLines']);
        }

        $this->view('accounting.reconciliation.index', $data);
    }

    public function import(): void
    {
        $account = $this->postedAccount();
        if (!$account) {
            return;
        }

        $file = $_FILES['statement'] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            Session::set('error', 'Choisissez un fichier CSV de relevé à importer.');
            $this->back($account);
            return;
        }

        if ($file['size'] > BankStatementParser::MAX_BYTES) {
            Session::set('error', 'Fichier trop volumineux (2 Mo maximum).');
            $this->back($account);
            return;
        }

        $parsed = BankStatementParser::parse((string) file_get_contents($file['tmp_name']));
        if (!$parsed['lines']) {
            Session::set('error', 'Aucune ligne exploitable. ' . implode(' ', array_slice($parsed['errors'], 0, 3)));
            $this->back($account);
            return;
        }

        $r = BankStatement::import((int) $account['company_id'], (int) $account['id'], $parsed['lines']);
        $msg = $r['added'] . ' ligne(s) importée(s)';
        if ($r['duplicates']) {
            $msg .= ', ' . $r['duplicates'] . ' déjà présente(s) ignorée(s)';
        }
        if ($parsed['errors']) {
            $msg .= ', ' . count($parsed['errors']) . ' ligne(s) illisible(s) : ' . implode(' ', array_slice($parsed['errors'], 0, 2));
        }
        Session::set('success', $msg . '.');
        $this->back($account);
    }

    public function auto(): void
    {
        $account = $this->postedAccount();
        if (!$account) {
            return;
        }

        $cid = (int) $account['company_id'];
        $aid = (int) $account['id'];
        $pairs = $this->suggest(BankStatement::lines($cid, $aid, false), BankStatement::openEntryLines($cid, $aid));
        $n = 0;
        foreach ($pairs as $lineId => $entryLineId) {
            if (BankStatement::match((int) $lineId, (int) $entryLineId)) {
                $n++;
            }
        }

        Session::set('success', $n > 0 ? "$n ligne(s) rapprochée(s) automatiquement." : 'Aucune correspondance exacte trouvée (même montant, ±' . self::WINDOW_DAYS . ' jours).');
        $this->back($account);
    }

    public function reconcile(string $id): void
    {
        $line = $this->ownedLine((int) $id);
        if (!$line) {
            return;
        }

        $el = BankStatement::entryLineForAccount((int) ($_POST['entry_line_id'] ?? 0), (int) $line['company_id'], (int) $line['account_id']);
        if (!$el) {
            Session::set('error', 'Écriture introuvable pour ce compte de banque.');
        } elseif (abs((float) $el['amount'] - (float) $line['amount']) > 0.004) {
            Session::set('error', 'Les montants ne correspondent pas : rapprochement refusé.');
        } elseif (!BankStatement::match((int) $line['id'], (int) $el['id'])) {
            Session::set('error', 'Cette écriture est déjà rapprochée.');
        } else {
            Session::set('success', 'Ligne rapprochée.');
        }

        $this->back(['id' => $line['account_id']]);
    }

    public function unreconcile(string $id): void
    {
        $line = $this->ownedLine((int) $id);
        if (!$line) {
            return;
        }

        BankStatement::unmatch((int) $line['id']);
        Session::set('success', 'Rapprochement annulé.');
        $this->back(['id' => $line['account_id']]);
    }

    public function delete(string $id): void
    {
        $line = $this->ownedLine((int) $id);
        if (!$line) {
            return;
        }

        Session::set(BankStatement::deleteLine((int) $line['id']) ? 'success' : 'error',
            $line['entry_line_id'] ? 'Annulez d’abord le rapprochement.' : 'Ligne de relevé supprimée.');
        $this->back(['id' => $line['account_id']]);
    }

    /** @return array<int,int> ligne de relevé => ligne d'écriture (montant identique, date la plus proche) */
    private function suggest(array $pending, array $open): array
    {
        $used = [];
        $out = [];
        foreach ($pending as $p) {
            $best = null;
            $bestGap = PHP_INT_MAX;
            foreach ($open as $o) {
                if (isset($used[$o['id']]) || abs((float) $o['amount'] - (float) $p['amount']) > 0.004) {
                    continue;
                }
                $gap = abs((int) round((strtotime($o['entry_date']) - strtotime($p['line_date'])) / 86400));
                if ($gap <= self::WINDOW_DAYS && $gap < $bestGap) {
                    $best = $o;
                    $bestGap = $gap;
                }
            }
            if ($best) {
                $used[$best['id']] = true;
                $out[(int) $p['id']] = (int) $best['id'];
            }
        }

        return $out;
    }

    private function postedAccount(): ?array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(url('accounting/reconciliation'));
            return null;
        }

        $id = (int) ($_POST['account_id'] ?? 0);
        foreach (BankStatement::bankAccounts(Auth::companyIds()) as $a) {
            if ((int) $a['id'] === $id) {
                return $a;
            }
        }

        Session::set('error', 'Compte bancaire inconnu ou non autorisé.');
        $this->redirect(url('accounting/reconciliation'));
        return null;
    }

    private function ownedLine(int $id): ?array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(url('accounting/reconciliation'));
            return null;
        }

        BankStatement::ensureSchema();
        $line = BankStatement::findLine($id);
        $ids = Auth::companyIds();
        if (!$line || ($ids !== null && !in_array((int) $line['company_id'], array_map('intval', $ids), true))) {
            Session::set('error', 'Ligne de relevé introuvable.');
            $this->redirect(url('accounting/reconciliation'));
            return null;
        }

        return $line;
    }

    private function back(array $account): void
    {
        $this->redirect(url('accounting/reconciliation?account_id=' . (int) $account['id']));
    }
}
