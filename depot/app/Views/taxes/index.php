<?php
$taxes = $taxes ?? [];
$totalTaxes = 0; $paidTaxes = 0; $pendingTaxes = 0; $overdueTaxes = 0;
foreach ($taxes as $tax) {
    $amount = (float) ($tax['tax_amount'] ?? 0);
    $s = $tax['status'] ?? '';
    $totalTaxes += $amount;
    if ($s === 'paid') $paidTaxes += $amount;
    elseif ($s === 'overdue') $overdueTaxes += $amount;
    else $pendingTaxes += $amount;
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Gestion fiscale</h1>
        <p class="page-subtitle">Suivi des obligations fiscales de l'entreprise.</p>
    </div>
</div>

<section class="card mb-4">
    <div class="card-body">
        <form action="<?= url('taxes') ?>" method="GET" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label for="exercise_id" class="form-label">Exercice comptable</label>
                <select id="exercise_id" name="exercise_id" class="form-select" required>
                    <option value="">Choisir un exercice</option>
                    <?php foreach (($exercises ?? []) as $exercise): ?>
                        <option value="<?= (int) $exercise['id'] ?>" <?= (int) ($selectedExercise ?? 0) === (int) $exercise['id'] ? 'selected' : '' ?>>
                            <?= e($exercise['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Afficher les données fiscales</button>
            </div>
        </form>
    </div>
</section>

<div class="dashboard-stats mb-4" style="grid-template-columns: repeat(4, minmax(0,1fr));">
    <div class="stat-card"><span class="stat-icon primary"><i class="fas fa-file-invoice-dollar"></i></span><span class="stat-info"><span class="stat-label">Total impôts</span><strong class="stat-value" style="font-size:15px;"><?= number_format($totalTaxes, 0, ',', ' ') ?> FCFA</strong></span></div>
    <div class="stat-card"><span class="stat-icon success"><i class="fas fa-check-circle"></i></span><span class="stat-info"><span class="stat-label">Payés</span><strong class="stat-value" style="font-size:15px;"><?= number_format($paidTaxes, 0, ',', ' ') ?> FCFA</strong></span></div>
    <div class="stat-card"><span class="stat-icon warning"><i class="fas fa-clock"></i></span><span class="stat-info"><span class="stat-label">En attente</span><strong class="stat-value" style="font-size:15px;"><?= number_format($pendingTaxes, 0, ',', ' ') ?> FCFA</strong></span></div>
    <div class="stat-card"><span class="stat-icon danger"><i class="fas fa-exclamation-circle"></i></span><span class="stat-info"><span class="stat-label">En retard</span><strong class="stat-value" style="font-size:15px;"><?= number_format($overdueTaxes, 0, ',', ' ') ?> FCFA</strong></span></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <a href="<?= url('taxes/vat') . '?exercise_id=' . (int) ($selectedExercise ?? 0) ?>" class="text-decoration-none">
            <div class="card h-100"><div class="card-body"><div class="d-flex align-items-center mb-3"><i class="fas fa-percentage fa-2x text-primary me-3"></i><h5 class="mb-0 text-dark">TVA</h5></div><p class="text-muted mb-0">Consulter les informations relatives à la taxe sur la valeur ajoutée.</p></div></div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= url('taxes/declarations') . '?exercise_id=' . (int) ($selectedExercise ?? 0) ?>" class="text-decoration-none">
            <div class="card h-100"><div class="card-body"><div class="d-flex align-items-center mb-3"><i class="fas fa-file-alt fa-2x text-success me-3"></i><h5 class="mb-0 text-dark">Déclarations fiscales</h5></div><p class="text-muted mb-0">Consulter les déclarations fiscales de l'entreprise.</p></div></div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= url('taxes/deadlines') . '?exercise_id=' . (int) ($selectedExercise ?? 0) ?>" class="text-decoration-none">
            <div class="card h-100"><div class="card-body"><div class="d-flex align-items-center mb-3"><i class="fas fa-calendar-alt fa-2x text-warning me-3"></i><h5 class="mb-0 text-dark">Échéances fiscales</h5></div><p class="text-muted mb-0">Suivre les dates limites des obligations fiscales.</p></div></div>
        </a>
    </div>
</div>

<section class="card">
    <div class="card-header"><h2 class="card-title">Suivi des impôts et taxes</h2><span><?= count($taxes) ?> entrée(s)</span></div>
    <div class="card-body">
        <?php if (!empty($taxes)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Type d'impôt</th><th>Période</th><th>Date limite</th><th class="text-end">Montant (FCFA)</th><th>Statut</th></tr></thead>
                    <tbody>
                        <?php foreach ($taxes as $tax): ?>
                            <?php
                            $status = $tax['status'] ?? '';
                            $statusLabels = ['paid' => 'Payé', 'pending' => 'En attente', 'overdue' => 'En retard'];
                            $statusClasses = ['paid' => 'bg-success', 'pending' => 'bg-warning text-dark', 'overdue' => 'bg-danger'];
                            ?>
                            <tr>
                                <td><?= e($tax['name'] ?? '') ?></td>
                                <td><?= e($tax['tax_period'] ?? '') ?></td>
                                <td><?= !empty($tax['due_date']) ? formatDate($tax['due_date']) : 'Non renseignée' ?></td>
                                <td class="text-end"><?= number_format((float) ($tax['tax_amount'] ?? 0), 2, ',', ' ') ?></td>
                                <td><span class="badge <?= $statusClasses[$status] ?? 'bg-secondary' ?>"><?= e($statusLabels[$status] ?? $status) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state"><i class="fas fa-file-invoice-dollar fa-3x"></i><p>Aucune donnée fiscale disponible.</p></div>
        <?php endif; ?>
    </div>
</section>
