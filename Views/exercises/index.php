<div class="page-header">
    <div>
        <h1 class="page-title">Exercices comptables</h1>
        <p class="page-subtitle">Gestion des exercices comptables des entreprises.</p>
    </div>
    <a href="<?= url('exercises/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nouvel exercice
    </a>
</div>

<section class="card exercise-list-card">
    <div class="card-header">
        <h2 class="card-title">Liste des exercices</h2>
        <span><?= count($exercises ?? []) ?> exercice(s)</span>
    </div>
    <div class="card-body exercise-list-body">
        <?php if (!empty($exercises)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>#</th><th>Entreprise</th><th>Nom de l'exercice</th><th>Date de début</th><th>Date de fin</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($exercises as $exercise): ?>
                            <?php
                            $status = $exercise['status'];
                            $statusLabels = [EXERCISE_OPEN => 'Ouvert', EXERCISE_CLOSED => 'Clôturé', EXERCISE_LOCKED => 'Verrouillé'];
                            $statusClasses = [EXERCISE_OPEN => 'bg-success', EXERCISE_CLOSED => 'bg-secondary', EXERCISE_LOCKED => 'bg-danger'];
                            ?>
                            <tr>
                                <td><?= e($exercise['id']) ?></td>
                                <td><?= e($exercise['company_name'] ?? 'Non renseignée') ?></td>
                                <td><strong><?= e($exercise['name']) ?></strong></td>
                                <td><?= formatDate($exercise['start_date']) ?></td>
                                <td><?= formatDate($exercise['end_date']) ?></td>
                                <td><span class="badge <?= $statusClasses[$status] ?? 'bg-secondary' ?>"><?= e($statusLabels[$status] ?? $status) ?></span></td>
                                <td class="text-end">
                                    <a href="<?= url('exercises/' . $exercise['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary" title="Modifier"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="exercise-empty-state">
                <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                <h5>Aucun exercice comptable</h5>
                <p class="text-muted">Aucun exercice comptable n'a encore été enregistré.</p>
                <a href="<?= url('exercises/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Créer un exercice</a>
            </div>
        <?php endif; ?>
    </div>
</section>
