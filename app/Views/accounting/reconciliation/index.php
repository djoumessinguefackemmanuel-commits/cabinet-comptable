<?php
$cur = $current ?? null;
$s = $summary ?? null;
$fmt = fn($v) => formatMoney($v);
?>
<div class="page-header">
    <div><h1 class="page-title">Rapprochement bancaire</h1><p class="page-subtitle">Importez le relevé de la banque et pointez-le avec vos écritures.</p></div>
</div>

<?php if (!$cur): ?>
<section class="card"><div class="card-body"><div class="empty-state">
    <i class="fas fa-university" aria-hidden="true"></i>
    <h2>Aucun compte de banque</h2>
    <p>Créez un compte de trésorerie (classe 5, ex. 521 Banque) dans le plan comptable, puis enregistrez des écritures dessus.</p>
</div></div></section>
<?php else: ?>

<section class="card" style="margin-bottom:1rem"><div class="card-body">
    <form method="get" class="form-row" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:end">
        <div style="flex:1;min-width:220px">
            <label for="account_id">Compte de banque</label>
            <select name="account_id" id="account_id" class="form-control" onchange="this.form.submit()">
                <?php foreach ($bankAccounts as $a): ?>
                    <option value="<?= (int) $a['id'] ?>" <?= (int) $a['id'] === (int) $cur['id'] ? 'selected' : '' ?>>
                        <?= e($a['company_name'] . ' — ' . $a['account_number'] . ' ' . $a['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div></section>

<section class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1rem">
    <div class="card"><div class="card-body"><small>Solde comptable</small><h3><?= $fmt($s['book_balance']) ?></h3></div></div>
    <div class="card"><div class="card-body"><small>Solde du relevé importé</small><h3><?= $fmt($s['bank_balance']) ?></h3></div></div>
    <div class="card"><div class="card-body"><small>À rapprocher</small><h3><?= (int) $s['pending'] ?> ligne(s)</h3></div></div>
    <div class="card"><div class="card-body"><small>Rapprochées</small><h3><?= (int) $s['reconciled'] ?></h3></div></div>
</section>

<section class="card" style="margin-bottom:1rem"><div class="card-body">
    <h2 style="font-size:1.1rem">Importer un relevé (CSV)</h2>
    <form method="post" action="<?= url('accounting/reconciliation/import') ?>" enctype="multipart/form-data" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
        <?= csrfField() ?>
        <input type="hidden" name="account_id" value="<?= (int) $cur['id'] ?>">
        <input type="file" name="statement" accept=".csv,.txt,text/csv" required class="form-control" style="flex:1;min-width:220px">
        <button class="btn btn-primary" type="submit"><i class="fas fa-upload"></i> Importer</button>
    </form>
    <p style="margin:.75rem 0 0;font-size:.85rem;opacity:.8">
        Exportez le relevé de votre banque en CSV. Colonnes reconnues : <strong>Date ; Libellé ; Débit ; Crédit</strong>
        (ou <strong>Date ; Libellé ; Montant</strong>, négatif = sortie). Séparateur ; , ou tabulation. Les lignes déjà importées sont ignorées.
    </p>
    <?php if ($s['pending'] > 0): ?>
    <form method="post" action="<?= url('accounting/reconciliation/auto') ?>" style="margin-top:.75rem">
        <?= csrfField() ?>
        <input type="hidden" name="account_id" value="<?= (int) $cur['id'] ?>">
        <button class="btn btn-secondary" type="submit"><i class="fas fa-magic"></i> Rapprochement automatique (<?= count($suggestions) ?> proposition(s))</button>
    </form>
    <?php endif; ?>
</div></section>

<section class="card" style="margin-bottom:1rem"><div class="card-body">
    <h2 style="font-size:1.1rem">Lignes du relevé à rapprocher</h2>
    <?php if (!$pending): ?>
        <p>Aucune ligne en attente.</p>
    <?php else: ?>
    <div class="table-responsive"><table class="table">
        <thead><tr><th>Date</th><th>Libellé</th><th style="text-align:right">Montant</th><th>Écriture comptable</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($pending as $p):
            $sug = $suggestions[(int) $p['id']] ?? null; ?>
            <tr>
                <td><?= e($p['line_date']) ?></td>
                <td><?= e($p['label']) ?><?= $p['reference'] ? ' <small>(' . e($p['reference']) . ')</small>' : '' ?></td>
                <td style="text-align:right;white-space:nowrap"><?= $fmt($p['amount']) ?></td>
                <td>
                    <form method="post" action="<?= url('accounting/reconciliation/' . (int) $p['id'] . '/reconcile') ?>" style="display:flex;gap:.4rem;flex-wrap:wrap">
                        <?= csrfField() ?>
                        <select name="entry_line_id" class="form-control" style="min-width:200px" required>
                            <option value="">— choisir —</option>
                            <?php foreach ($openLines as $o):
                                if (abs((float) $o['amount'] - (float) $p['amount']) > 0.004) { continue; } ?>
                                <option value="<?= (int) $o['id'] ?>" <?= $sug === (int) $o['id'] ? 'selected' : '' ?>>
                                    <?= e($o['entry_date'] . ' · ' . $o['reference'] . ' · ' . mb_substr((string) $o['description'], 0, 40)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-primary btn-sm" type="submit">Rapprocher</button>
                    </form>
                </td>
                <td>
                    <form method="post" action="<?= url('accounting/reconciliation/' . (int) $p['id'] . '/delete') ?>" onsubmit="return confirm('Supprimer cette ligne du relevé ?')">
                        <?= csrfField() ?><button class="btn btn-sm" type="submit" title="Supprimer"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php endif; ?>
</div></section>

<section class="card"><div class="card-body">
    <h2 style="font-size:1.1rem">Lignes rapprochées</h2>
    <?php if (!$done): ?>
        <p>Aucun rapprochement pour l’instant.</p>
    <?php else: ?>
    <div class="table-responsive"><table class="table">
        <thead><tr><th>Date</th><th>Libellé</th><th style="text-align:right">Montant</th><th>Écriture</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($done as $d): ?>
            <tr>
                <td><?= e($d['line_date']) ?></td>
                <td><?= e($d['label']) ?></td>
                <td style="text-align:right;white-space:nowrap"><?= $fmt($d['amount']) ?></td>
                <td><?= e(($d['entry_reference'] ?? '') . ' · ' . ($d['entry_date'] ?? '')) ?></td>
                <td>
                    <form method="post" action="<?= url('accounting/reconciliation/' . (int) $d['id'] . '/unreconcile') ?>">
                        <?= csrfField() ?><button class="btn btn-sm" type="submit">Annuler</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php endif; ?>
</div></section>
<?php endif; ?>
