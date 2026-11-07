
<?php
$title = 'Tableau de bord expert-comptable';

$totalCompanies = $totalCompanies ?? 0;
$totalExercises = $totalExercises ?? 0;
$totalEntries = $totalEntries ?? 0;
$recentActivities = $recentActivities ?? [];
?>

<div class="page-header">
    <div>
        <h1>Tableau de bord</h1>
        <p>Bienvenue dans votre espace expert-comptable.</p>
    </div>
</div>

<div class="dashboard-stats">

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-building"></i>
        </div>
        <div class="stat-info">
            <h3><?= number_format((int) $totalCompanies) ?></h3>
            <p>Entreprises suivies</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="stat-info">
            <h3><?= number_format((int) $totalExercises) ?></h3>
            <p>Exercices comptables</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-book"></i>
        </div>
        <div class="stat-info">
            <h3><?= number_format((int) $totalEntries) ?></h3>
            <p>Écritures comptables</p>
        </div>
    </div>

</div>

<div class="dashboard-section">

    <div class="section-header">
        <h2>Activités récentes</h2>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">

            <thead>
                <tr>
                    <th>Action</th>
                    <th>Description</th>
                    <th>Date</th>
                </tr>
            </thead>

            <tbody>

                <?php if (!empty($recentActivities)): ?>

                    <?php foreach ($recentActivities as $activity): ?>

                        <tr>
                            <td>
                                <?= e($activity['action'] ?? '') ?>
                            </td>

                            <td>
                                <?= e($activity['description'] ?? '') ?>
                            </td>

                            <td>
                                <?= !empty($activity['created_at'])
                                    ? e(formatDateTime($activity['created_at']))
                                    : '-' ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="3" class="text-center">
                            Aucune activité enregistrée pour le moment.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>
    </div>

</div>
