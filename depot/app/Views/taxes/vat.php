<?php
$vatData = $vatData ?? [];
$collectedVat = (float) ($vatData['collected_vat'] ?? 0);
$deductibleVat = (float) ($vatData['deductible_vat'] ?? 0);
$vatToPay = $collectedVat - $deductibleVat;
$operations = $vatData['operations'] ?? [];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Gestion de la TVA</h1>
        <p class="page-subtitle">Suivi de la taxe sur la valeur ajoutée.</p>
    </div>
    <a href="<?= url('taxes') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
</div>

<section class="card mb-4">
    <div class="card-header"><h2 class="card-title">Période de consultation</h2></div>
    <div class="card-body">
        <form action="<?= url('taxes/vat') ?>" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-3"><label for="exercise_id" class="form-label">Exercice comptable</label><select name="exercise_id" id="exercise_id" class="form-select" required><option value="">Choisir un exercice</option><?php foreach (($exercises ?? []) as $exercise): ?><option value="<?= (int) $exercise['id'] ?>" <?= (int) ($selectedExercise ?? 0) === (int) $exercise['id'] ? 'selected' : '' ?>><?= e($exercise['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label for="start_date" class="form-label">Date de début</label><input type="date" name="start_date" id="start_date" class="form-control" value="<?= e($_GET['start_date'] ?? '') ?>"></div>
                <div class="col-md-3"><label for="end_date" class="form-label">Date de fin</label><input type="date" name="end_date" id="end_date" class="form-control" value="<?= e($_GET['end_date'] ?? '') ?>"></div>
                <div class="col-md-3"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Consulter</button></div>
            </div>
        </form>
    </div>
</section>
<?php if (!empty($errors)): ?><div class="alert alert-danger" role="alert"><?php foreach ($errors as $error): ?><p class="mb-0"><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>

<div class="dashboard-stats mb-4" style="grid-template-columns: repeat(3, minmax(0,1fr));">
    <div class="stat-card"><span class="stat-icon primary"><i class="fas fa-percentage"></i></span><span class="stat-info"><span class="stat-label">TVA collectée</span><strong class="stat-value" style="font-size:15px;"><?= number_format($collectedVat, 2, ',', ' ') ?> FCFA</strong><span class="stat-description">TVA facturée sur les ventes</span></span></div>
    <div class="stat-card"><span class="stat-icon success"><i class="fas fa-minus-circle"></i></span><span class="stat-info"><span class="stat-label">TVA déductible</span><strong class="stat-value" style="font-size:15px;"><?= number_format($deductibleVat, 2, ',', ' ') ?> FCFA</strong><span class="stat-description">TVA récupérable sur les achats</span></span></div>
    <div class="stat-card"><span class="stat-icon <?= $vatToPay >= 0 ? 'danger' : 'success' ?>"><i class="fas fa-balance-scale"></i></span><span class="stat-info"><span class="stat-label">Solde de TVA</span><strong class="stat-value" style="font-size:15px;"><?= number_format(abs($vatToPay), 2, ',', ' ') ?> FCFA</strong><span class="stat-description"><?= $vatToPay > 0 ? 'TVA à payer' : ($vatToPay < 0 ? 'Crédit de TVA' : 'Aucun solde') ?></span></span></div>
</div>

<section class="card">
    <div class="card-header"><h2 class="card-title">Détail des opérations soumises à la TVA</h2></div>
    <div class="card-body">
        <?php if (!empty($operations)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Date</th><th>Référence</th><th>Nature de l'opération</th><th class="text-end">Montant HT (FCFA)</th><th class="text-end">Taux TVA</th><th class="text-end">Montant TVA (FCFA)</th></tr></thead>
                    <tbody>
                        <?php foreach ($operations as $operation): ?>
                            <tr>
                                <td><?= !empty($operation['date']) ? formatDate($operation['date']) : '' ?></td>
                                <td><?= e($operation['reference'] ?? '') ?></td>
                                <td><?= e($operation['description'] ?? '') ?></td>
                                <td class="text-end"><?= number_format((float) ($operation['amount_ht'] ?? 0), 2, ',', ' ') ?></td>
                                <td class="text-end"><?= number_format((float) ($operation['vat_rate'] ?? 0), 2, ',', ' ') ?> %</td>
                                <td class="text-end"><?= number_format((float) ($operation['vat_amount'] ?? 0), 2, ',', ' ') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state"><i class="fas fa-percentage fa-3x"></i><p>Aucune opération relative à la TVA pour cette période.</p></div>
        <?php endif; ?>
    </div>
</section>
