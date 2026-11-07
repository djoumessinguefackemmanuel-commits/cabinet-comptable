<?php
$declarations = $declarations ?? [];
$totalDeclarations = count($declarations);
$submittedDeclarations = 0; $pendingDeclarations = 0; $lateDeclarations = 0;
foreach ($declarations as $d) {
    $s = $d['status'] ?? '';
    if ($s === 'declared' || $s === 'paid') $submittedDeclarations++;
    elseif ($s === 'overdue') $lateDeclarations++;
    else $pendingDeclarations++;
}
$statusLabels = ['declared' => 'Déposée', 'paid' => 'Payée', 'pending' => 'En attente', 'overdue' => 'En retard'];
$statusClasses = ['declared' => 'bg-success', 'paid' => 'bg-success', 'pending' => 'bg-warning text-dark', 'overdue' => 'bg-danger'];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Déclarations fiscales</h1>
        <p class="page-subtitle">Suivi des déclarations fiscales de l'entreprise.</p>
    </div>
    <a href="<?= url('taxes') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
</div>

<div class="dashboard-stats mb-4" style="grid-template-columns: repeat(4, minmax(0,1fr));">
    <div class="stat-card"><span class="stat-icon primary"><i class="fas fa-file-alt"></i></span><span class="stat-info"><span class="stat-label">Total</span><strong class="stat-value"><?= $totalDeclarations ?></strong></span></div>
    <div class="stat-card"><span class="stat-icon success"><i class="fas fa-check-circle"></i></span><span class="stat-info"><span class="stat-label">Déposées</span><strong class="stat-value"><?= $submittedDeclarations ?></strong></span></div>
    <div class="stat-card"><span class="stat-icon warning"><i class="fas fa-clock"></i></span><span class="stat-info"><span class="stat-label">En attente</span><strong class="stat-value"><?= $pendingDeclarations ?></strong></span></div>
    <div class="stat-card"><span class="stat-icon danger"><i class="fas fa-exclamation-circle"></i></span><span class="stat-info"><span class="stat-label">En retard</span><strong class="stat-value"><?= $lateDeclarations ?></strong></span></div>
</div>

<section class="card">
    <div class="card-header"><h2 class="card-title">Liste des déclarations fiscales</h2><span><?= $totalDeclarations ?> déclaration(s)</span></div>
    <div class="card-body">
        <?php if (!empty($declarations)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Type de déclaration</th><th>Période</th><th>Date limite</th><th>Date de dépôt</th><th class="text-end">Montant (FCFA)</th><th>Statut</th></tr></thead>
                    <tbody>
                        <?php foreach ($declarations as $declaration): ?>
                            <?php $status = $declaration['status'] ?? ''; ?>
                            <tr>
                                <td><?= e($declaration['name'] ?? '') ?></td>
                                <td><?= e($declaration['tax_period'] ?? '') ?></td>
                                <td><?= !empty($declaration['due_date']) ? formatDate($declaration['due_date']) : 'Non renseignée' ?></td>
                                <td><?= !empty($declaration['declaration_date']) ? formatDate($declaration['declaration_date']) : 'Non déposée' ?></td>
                                <td class="text-end"><?= number_format((float) ($declaration['tax_amount'] ?? 0), 2, ',', ' ') ?></td>
                                <td><span class="badge <?= $statusClasses[$status] ?? 'bg-secondary' ?>"><?= e($statusLabels[$status] ?? $status) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state"><i class="fas fa-file-alt fa-3x"></i><p>Aucune déclaration fiscale disponible.</p></div>
        <?php endif; ?>
    </div>
</section>
