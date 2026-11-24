<?php $fmt = fn($v) => formatMoney($v); $cats = \App\Models\FixedAsset::CATEGORIES;
$tc = $tv = $tn = 0; foreach ($assets as $a) { $tc += $a['cost']; $tv += $a['cumul']; $tn += $a['vnc']; } ?>
<div class="page-header" style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:.5rem">
    <div><h1 class="page-title">Immobilisations</h1><p class="page-subtitle">Registre des biens et amortissements (linéaire, prorata temporis).</p></div>
    <div><a class="btn btn-primary" href="<?= url('fixed-assets/create') ?>"><i class="fas fa-plus"></i> Nouvelle immobilisation</a></div>
</div>

<section style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1rem">
    <div class="card"><div class="card-body"><small>Valeur d’acquisition</small><h3><?= $fmt($tc) ?></h3></div></div>
    <div class="card"><div class="card-body"><small>Amortissements cumulés (aujourd’hui)</small><h3><?= $fmt($tv) ?></h3></div></div>
    <div class="card"><div class="card-body"><small>Valeur nette comptable</small><h3><?= $fmt($tn) ?></h3></div></div>
</section>

<section class="card" style="margin-bottom:1rem"><div class="card-body">
    <?php if (!$assets): ?>
        <div class="empty-state"><i class="fas fa-building"></i><h2>Aucune immobilisation</h2><p>Enregistrez vos biens (matériel, véhicules, logiciels…) pour calculer leurs amortissements.</p></div>
    <?php else: ?>
    <div class="table-responsive"><table class="table">
        <thead><tr><th>Réf.</th><th>Désignation</th><th>Entreprise</th><th>Acquis le</th><th style="text-align:right">Coût</th><th>Durée</th><th style="text-align:right">VNC</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($assets as $a): ?>
            <tr>
                <td><?= e($a['reference']) ?></td>
                <td><a href="<?= url('fixed-assets/' . (int) $a['id']) ?>"><?= e($a['name']) ?></a>
                    <small style="display:block;opacity:.7"><?= e($cats[$a['category']]['label'] ?? $a['category']) ?></small></td>
                <td><?= e($a['company_name']) ?></td>
                <td><?= e($a['acquisition_date']) ?></td>
                <td style="text-align:right;white-space:nowrap"><?= $fmt($a['cost']) ?></td>
                <td><?= (int) $a['useful_life_years'] ?> ans</td>
                <td style="text-align:right;white-space:nowrap"><?= $fmt($a['vnc']) ?></td>
                <td><?= $a['disposal_date'] ? '<span class="badge bg-secondary">Cédé ' . e($a['disposal_date']) . '</span>' : '' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php endif; ?>
</div></section>

<?php if ($assets && $exercises): ?>
<section class="card"><div class="card-body">
    <h2 style="font-size:1.1rem">Comptabiliser les dotations d’un exercice</h2>
    <p style="font-size:.9rem;opacity:.8">Crée une écriture <strong>en brouillon</strong> (débit 6813, crédit comptes d’amortissement 28x) que vous pouvez vérifier puis valider. Une dotation n’est comptabilisée qu’une fois par bien et par exercice.</p>
    <form method="post" action="<?= url('fixed-assets/post') ?>" style="display:flex;gap:.5rem;flex-wrap:wrap">
        <?= csrfField() ?>
        <select name="exercise_id" class="form-control" style="max-width:420px" required>
            <?php foreach ($exercises as $x): ?>
                <option value="<?= (int) $x['id'] ?>"><?= e($x['company_name'] . ' — ' . $x['name'] . ' (' . $x['start_date'] . ' → ' . $x['end_date'] . ')') ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-secondary" type="submit"><i class="fas fa-calculator"></i> Générer l’écriture</button>
    </form>
</div></section>
<?php endif; ?>
