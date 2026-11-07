
<?php
$title = 'Tableau de bord comptable';

$totalEntries = $totalEntries ?? 0;
$totalJournals = $totalJournals ?? 0;
$totalExercises = $totalExercises ?? 0;
$recentEntries = $recentEntries ?? [];
?>

<div class="page-header">
    <div>
        <h1>Tableau de bord</h1>
        <p>Bienvenue dans votre espace comptable.</p>
    </div>
</div>

<div class="dashboard-stats">

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-book"></i>
        </div>
        <div class="stat-info">
            <h3><?= number_format((int) $totalEntries) ?></h3>
            <p>Écritures comptables</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-book-open"></i>
        </div>
        <div class="stat-info">
            <h3><?= number_format((int) $totalJournals) ?></h3>
            <p>Journaux comptables</p>
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

</div>

<div class="dashboard-section">

    <div class="section-header">
        <h2>Écritures récentes</h2>
    </div>

    <div class="table-responsive">

        <table class="table table-hover">

            <thead>
                <tr>
                    <th>Référence</th>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Montant</th>
                    <th>Statut</th>
                </tr>
            </thead>

            <tbody>

                <?php if (!empty($recentEntries)): ?>

                    <?php foreach ($recentEntries as $entry): ?>

                        <tr>
                            <td>
                                <?= e($entry['reference'] ?? '-') ?>
                            </td>

                            <td>
                                <?= !empty($entry['entry_date'])
                                    ? e(formatDate($entry['entry_date']))
                                    : '-' ?>
                            </td>

                            <td>
                                <?= e($entry['description'] ?? '') ?>
                            </td>

                            <td>
                                <?= formatMoney($entry['amount'] ?? 0) ?>
                            </td>

                            <td>
                                <?= e($entry['status'] ?? '-') ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="5" class="text-center">
                            Aucune écriture comptable enregistrée pour le moment.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>
