<?php $fmt = fn($v) => formatMoney($v); ?>
<div class="page-header" style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:.5rem">
    <div><h1 class="page-title"><?= e($asset['name']) ?></h1>
    <p class="page-subtitle"><?= e($asset['reference']) ?> · <?= e($asset['company_name']) ?> · <?= e($category['label'] ?? $asset['category']) ?></p></div>
    <div><a class="btn btn-outline-secondary" href="<?= url('fixed-assets') ?>">Retour</a></div>
</div>

<section style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1rem">
    <div class="card"><div class="card-body"><small>Acquisition</small><h3><?= e($asset['acquisition_date']) ?></h3></div></div>
    <div class="card"><div class="card-body"><small>Coût</small><h3><?= $fmt($asset['cost']) ?></h3></div></div>
    <div class="card"><div class="card-body"><small>Durée</small><h3><?= (int) $asset['useful_life_years'] ?> ans</h3></div></div>
    <div class="card"><div class="card-body"><small>VNC aujourd’hui</small><h3><?= $fmt($asset['cost'] - $cumul) ?></h3></div></div>
</section>

<section class="card" style="margin-bottom:1rem"><div class="card-body">
    <h2 style="font-size:1.1rem">Plan d’amortissement</h2>
    <div class="table-responsive"><table class="table">
        <thead><tr><th>Année</th><th style="text-align:right">Dotation</th><th style="text-align:right">Cumul</th><th style="text-align:right">VNC fin d’année</th></tr></thead>
        <tbody><?php foreach ($schedule as $r): ?>
            <tr><td><?= (int) $r['year'] ?></td><td style="text-align:right"><?= $fmt($r['dotation']) ?></td><td style="text-align:right"><?= $fmt($r['cumul']) ?></td><td style="text-align:right"><?= $fmt($r['vnc']) ?></td></tr>
        <?php endforeach; ?></tbody>
    </table></div>
</div></section>

<section class="card" style="margin-bottom:1rem"><div class="card-body">
    <h2 style="font-size:1.1rem">Dotations comptabilisées</h2>
    <?php if (!$postings): ?><p>Aucune pour l’instant.</p><?php else: ?>
    <ul><?php foreach ($postings as $p): ?>
        <li><?= e($p['exercise_name']) ?> : <?= $fmt($p['amount']) ?> — <a href="<?= url('accounting/entries/' . (int) $p['entry_id']) ?>"><?= e($p['entry_reference']) ?></a></li>
    <?php endforeach; ?></ul><?php endif; ?>
</div></section>

<section class="card"><div class="card-body" style="display:flex;gap:1rem;flex-wrap:wrap">
    <?php if (!$asset['disposal_date']): ?>
    <form method="post" action="<?= url('fixed-assets/' . (int) $asset['id'] . '/dispose') ?>" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
        <?= csrfField() ?>
        <input class="form-control" type="date" name="disposal_date" required style="max-width:200px">
        <button class="btn btn-outline-secondary" type="submit">Enregistrer la cession</button>
    </form>
    <?php else: ?><span class="badge bg-secondary">Cédé le <?= e($asset['disposal_date']) ?></span><?php endif; ?>
    <?php if (!$postings): ?>
    <form method="post" action="<?= url('fixed-assets/' . (int) $asset['id'] . '/delete') ?>" onsubmit="return confirm('Supprimer cette immobilisation ?')">
        <?= csrfField() ?><button class="btn btn-outline-danger" type="submit"><i class="fas fa-trash"></i> Supprimer</button>
    </form>
    <?php endif; ?>
</div></section>
