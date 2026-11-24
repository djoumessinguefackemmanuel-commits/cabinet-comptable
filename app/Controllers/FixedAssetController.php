<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\Account;
use App\Models\Entry;
use App\Models\Exercise;
use App\Models\FixedAsset;
use App\Models\Journal;
use App\Services\AuditService;
use App\Services\Depreciation;

class FixedAssetController extends Controller
{
    public function index(): void
    {
        $assets = FixedAsset::allFor(Auth::companyIds());
        $today = date('Y-m-d');
        foreach ($assets as &$a) {
            $a['cumul'] = Depreciation::cumulative($a, $today);
            $a['vnc'] = round((float) $a['cost'] - $a['cumul'], 2);
        }
        unset($a);

        $companies = FixedAsset::companies(Auth::companyIds());
        $exercises = [];
        foreach ($companies as $c) {
            foreach (Exercise::getByCompany((int) $c['id']) as $x) {
                $exercises[] = $x + ['company_name' => $c['name']];
            }
        }

        $this->view('fixed_assets.index', ['title' => 'Immobilisations', 'assets' => $assets, 'exercises' => $exercises]);
    }

    public function create(): void
    {
        $this->view('fixed_assets.create', [
            'title' => 'Nouvelle immobilisation',
            'companies' => FixedAsset::companies(Auth::companyIds()),
            'categories' => FixedAsset::CATEGORIES,
        ]);
    }

    public function store(): void
    {
        $cid = (int) ($_POST['company_id'] ?? 0);
        $cat = (string) ($_POST['category'] ?? '');
        $name = trim((string) ($_POST['name'] ?? ''));
        $date = (string) ($_POST['acquisition_date'] ?? '');
        $cost = $this->num($_POST['cost'] ?? '');
        $residual = $this->num($_POST['residual_value'] ?? '0');
        $years = (int) ($_POST['useful_life_years'] ?? 0);

        $err = null;
        if (!$this->companyAllowed($cid)) {
            $err = 'Entreprise non autorisée.';
        } elseif (!isset(FixedAsset::CATEGORIES[$cat])) {
            $err = 'Choisissez une catégorie.';
        } elseif ($name === '' || mb_strlen($name) > 200) {
            $err = 'Indiquez la désignation du bien (200 caractères max).';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4))) {
            $err = 'Date d’acquisition invalide.';
        } elseif ($cost === null || $cost <= 0) {
            $err = 'Le coût d’acquisition doit être supérieur à 0.';
        } elseif ($residual === null || $residual < 0 || $residual >= $cost) {
            $err = 'La valeur résiduelle doit être inférieure au coût.';
        } elseif ($years < 1 || $years > 50) {
            $err = 'La durée d’amortissement doit être comprise entre 1 et 50 ans.';
        }

        if ($err) {
            Session::set('error', $err);
            $this->redirect(url('fixed-assets/create'));
            return;
        }

        FixedAsset::ensureSchema();
        $id = FixedAsset::create([
            'company_id' => $cid, 'reference' => FixedAsset::nextReference($cid), 'name' => $name, 'category' => $cat,
            'acquisition_date' => $date, 'cost' => $cost, 'residual_value' => $residual, 'useful_life_years' => $years,
        ]);

        if (!$id) {
            Session::set('error', 'Enregistrement impossible.');
            $this->redirect(url('fixed-assets/create'));
            return;
        }

        AuditService::log('create', 'fixed_asset', $id, 'Immobilisation créée : ' . $name);
        Session::set('success', 'Immobilisation enregistrée.');
        $this->redirect(url('fixed-assets/' . $id));
    }

    public function show(string $id): void
    {
        $asset = $this->owned((int) $id);
        if (!$asset) {
            return;
        }

        $this->view('fixed_assets.show', [
            'title' => $asset['name'],
            'asset' => $asset,
            'schedule' => Depreciation::schedule($asset),
            'postings' => FixedAsset::postings((int) $id),
            'category' => FixedAsset::CATEGORIES[$asset['category']] ?? null,
            'cumul' => Depreciation::cumulative($asset, date('Y-m-d')),
        ]);
    }

    public function dispose(string $id): void
    {
        $asset = $this->owned((int) $id);
        if (!$asset) {
            return;
        }

        $date = (string) ($_POST['disposal_date'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $date < $asset['acquisition_date']) {
            Session::set('error', 'Date de cession invalide.');
        } else {
            FixedAsset::dispose((int) $id, $date);
            AuditService::log('update', 'fixed_asset', (int) $id, 'Cession au ' . $date . ' : ' . $asset['name']);
            Session::set('success', 'Cession enregistrée : l’amortissement s’arrête à cette date. Comptabilisez la sortie du bien dans vos écritures.');
        }
        $this->redirect(url('fixed-assets/' . (int) $id));
    }

    public function delete(string $id): void
    {
        $asset = $this->owned((int) $id);
        if (!$asset) {
            return;
        }

        if (FixedAsset::delete((int) $id)) {
            AuditService::log('delete', 'fixed_asset', (int) $id, 'Immobilisation supprimée : ' . $asset['name']);
            Session::set('success', 'Immobilisation supprimée.');
            $this->redirect(url('fixed-assets'));
            return;
        }

        Session::set('error', 'Ce bien a déjà des dotations comptabilisées : suppression impossible.');
        $this->redirect(url('fixed-assets/' . (int) $id));
    }

    /** Génère l'écriture (brouillon) des dotations d'un exercice. */
    public function post(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(url('fixed-assets'));
            return;
        }

        $exercise = Exercise::findById((int) ($_POST['exercise_id'] ?? 0));
        if (!$exercise || !$this->companyAllowed((int) $exercise['company_id'])) {
            Session::set('error', 'Exercice introuvable.');
            $this->redirect(url('fixed-assets'));
            return;
        }
        if (($exercise['status'] ?? '') !== EXERCISE_OPEN) {
            Session::set('error', 'L’exercice doit être ouvert pour comptabiliser les dotations.');
            $this->redirect(url('fixed-assets'));
            return;
        }

        $cid = (int) $exercise['company_id'];
        $byAmort = [];
        $items = [];
        $total = 0.0;

        foreach (FixedAsset::activeByCompany($cid) as $a) {
            if (in_array((int) $exercise['id'], FixedAsset::postedExerciseIds((int) $a['id']), true)) {
                continue;
            }
            $amount = Depreciation::forPeriod($a, $exercise['start_date'], $exercise['end_date']);
            $cat = FixedAsset::CATEGORIES[$a['category']] ?? null;
            if ($amount <= 0 || !$cat) {
                continue;
            }
            $items[] = [$a, $amount];
            $byAmort[$cat['amort']] = ($byAmort[$cat['amort']] ?? 0) + $amount;
            $total += $amount;
        }

        if (!$items) {
            Session::set('warning', 'Aucune dotation à comptabiliser pour cet exercice (déjà faite ou rien à amortir).');
            $this->redirect(url('fixed-assets'));
            return;
        }

        $expense = $this->ensureAccount($cid, '6813', 'Dotations aux amortissements d’exploitation', 'expense');
        $lines = [['account_id' => $expense, 'description' => 'Dotations aux amortissements ' . $exercise['name'], 'debit' => round($total, 2), 'credit' => 0]];
        foreach ($byAmort as $number => $amount) {
            $label = 'Amortissements ' . strtolower(array_values(array_filter(FixedAsset::CATEGORIES, fn($c) => $c['amort'] === (string) $number))[0]['label']);
            $lines[] = ['account_id' => $this->ensureAccount($cid, (string) $number, $label, 'asset'), 'description' => $label, 'debit' => 0, 'credit' => round($amount, 2)];
        }

        $journalId = $this->journal($cid);
        $entryId = Entry::createWithLines([
            'company_id' => $cid, 'exercise_id' => (int) $exercise['id'], 'journal_id' => $journalId, 'user_id' => Auth::id(),
            'entry_date' => $exercise['end_date'], 'description' => 'Dotations aux amortissements ' . $exercise['name'], 'status' => ENTRY_DRAFT,
        ], $lines);

        if (!$entryId) {
            Session::set('error', 'Impossible de créer l’écriture de dotation.');
            $this->redirect(url('fixed-assets'));
            return;
        }

        foreach ($items as [$a, $amount]) {
            FixedAsset::recordPosting((int) $a['id'], (int) $exercise['id'], (int) $entryId, $amount);
        }

        AuditService::log('create', 'entry', (int) $entryId, 'Dotations aux amortissements ' . $exercise['name'] . ' : ' . number_format($total, 2, ',', ' '));
        Session::set('success', 'Écriture de dotation créée en brouillon (' . count($items) . ' bien(s)). Vérifiez puis validez-la.');
        $this->redirect(url('accounting/entries/' . $entryId));
    }

    private function ensureAccount(int $companyId, string $number, string $name, string $type): int
    {
        $a = Account::findByNumber($companyId, $number);
        if ($a) {
            return (int) $a['id'];
        }

        return (int) Account::create(['company_id' => $companyId, 'account_number' => $number, 'name' => $name, 'type' => $type]);
    }

    private function journal(int $companyId): int
    {
        foreach (Journal::getByCompany($companyId) as $j) {
            if ($j['type'] === 'miscellaneous') {
                return (int) $j['id'];
            }
        }

        return (int) Journal::create(['company_id' => $companyId, 'code' => 'OD', 'name' => 'Opérations diverses', 'type' => 'miscellaneous']);
    }

    private function owned(int $id): ?array
    {
        $asset = FixedAsset::find($id);
        if (!$asset || !$this->companyAllowed((int) $asset['company_id'])) {
            $this->renderError(404);
            return null;
        }
        return $asset;
    }

    private function companyAllowed(int $companyId): bool
    {
        $ids = Auth::companyIds();

        return $companyId > 0 && ($ids === null || in_array($companyId, array_map('intval', $ids), true));
    }

    private function num($v): ?float
    {
        $v = str_replace([' ', "\xc2\xa0"], '', (string) $v);
        $v = str_replace(',', '.', $v);

        return is_numeric($v) ? (float) $v : null;
    }
}
