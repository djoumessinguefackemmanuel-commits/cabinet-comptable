<?php
$deadlines = $deadlines ?? [];
$totalDeadlines = count($deadlines);
$upcomingDeadlines = 0; $overdueDeadlines = 0; $completedDeadlines = 0;
$today = date('Y-m-d');
foreach ($deadlines as $deadline) {
    $s = $deadline['status'] ?? '';
    $dueDate = $deadline['due_date'] ?? '';
    if ($s === 'paid') $completedDeadlines++;
    elseif ($s === 'overdue' || ($s !== 'paid' && $dueDate && $dueDate < $today)) $overdueDeadlines++;
    else $upcomingDeadlines++;
}
$statusLabels = ['paid' => 'Payée', 'declared' => 'Déclarée', 'pending' => 'À venir', 'overdue' => 'En retard'];
$statusClasses = ['paid' => 'bg-success', 'declared' => 'bg-info', 'pending' => 'bg-warning text-dark', 'overdue' => 'bg-danger'];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Échéances fiscales</h1>
        <p class="page-subtitle">Suivi des dates limites des obligations fiscales.</p>
    </div>
    <a href="<?= url('taxes') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
</div>

<div class="dashboard-stats mb-4" style="grid-template-columns: repeat(4, minmax(0,1fr));">
    <div class="stat-card"><span class="stat-icon primary"><i class="fas fa-calendar-alt"></i></span><span class="stat-info"><span class="stat-label">Total</span><strong class="stat-value"><?= $totalDeadlines ?></strong></span></div>
    <div class="stat-card"><span class="stat-icon warning"><i class="fas fa-clock"></i></span><span class="stat-info"><span class="stat-label">À venir</span><strong class="stat-value"><?= $upcomingDeadlines ?></strong></span></div>
    <div class="stat-card"><span class="stat-icon danger"><i class="fas fa-exclamation-circle"></i></span><span class="stat-info"><span class="stat-label">En retard</span><strong class="stat-value"><?= $overdueDeadlines ?></strong></span></div>
    <div class="stat-card"><span class="stat-icon success"><i class="fas fa-check-circle"></i></span><span class="stat-info"><span class="stat-label">Terminées</span><strong class="stat-value"><?= $completedDeadlines ?></strong></span></div>
</div>

<section class="card">
    <div class="card-header"><h2 class="card-title">Calendrier des échéances fiscales</h2><span><?= $totalDeadlines ?> échéance(s)</span></div>
    <div class="card-body">
        <form method="GET" action="<?= url('taxes/deadlines') ?>" class="row g-3 mb-4">
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
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Afficher les échéances</button>
            </div>
        </form>
        <?php if (!empty($deadlines)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Obligation fiscale</th><th>Description</th><th>Date limite</th><th class="text-end">Montant (FCFA)</th><th>Statut</th></tr></thead>
                    <tbody>
                        <?php foreach ($deadlines as $deadline): ?>
                            <?php
                            $status = $deadline['status'] ?? 'upcoming';
                            if ($status !== 'paid' && !empty($deadline['due_date']) && $deadline['due_date'] < $today) $status = 'overdue';
                            ?>
                            <tr>
                                <td><?= e($deadline['name'] ?? '') ?></td>
                                <td><?= e($deadline['description'] ?? '') ?></td>
                                <td><?= !empty($deadline['due_date']) ? formatDate($deadline['due_date']) : 'Non renseignée' ?></td>
                                <td class="text-end"><?= number_format((float) ($deadline['tax_amount'] ?? 0), 2, ',', ' ') ?></td>
                                <td><span class="badge <?= $statusClasses[$status] ?? 'bg-secondary' ?>"><?= e($statusLabels[$status] ?? $status) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state"><i class="fas fa-calendar-check fa-3x"></i><p>Aucune échéance fiscale disponible.</p></div>
        <?php endif; ?>
    </div>
</section>
