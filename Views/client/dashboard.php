
<?php
$title = 'Tableau de bord client';

$totalInvoices = $totalInvoices ?? 0;
$pendingInvoices = $pendingInvoices ?? 0;
$totalExercises = $totalExercises ?? 0;
$recentInvoices = $recentInvoices ?? [];
?>

<div class="page-header">
    <div>
        <h1>Tableau de bord</h1>
        <p>Bienvenue dans votre espace client.</p>
    </div>
</div>

<div class="dashboard-stats">

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div class="stat-info">
            <h3><?= number_format((int) $totalInvoices) ?></h3>
            <p>Total des factures</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-info">
            <h3><?= number_format((int) $pendingInvoices) ?></h3>
            <p>Factures en attente</p>
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
        <h2>Mes factures récentes</h2>
    </div>

    <div class="table-responsive">

        <table class="table table-hover">

            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Montant</th>
                    <th>Statut</th>
                </tr>
            </thead>

            <tbody>

                <?php if (!empty($recentInvoices)): ?>

                    <?php foreach ($recentInvoices as $invoice): ?>

                        <tr>
                            <td>
                                <?= e($invoice['invoice_number'] ?? '-') ?>
                            </td>

                            <td>
                                <?= e($invoice['description'] ?? '') ?>
                            </td>

                            <td>
                                <?= !empty($invoice['invoice_date'])
                                    ? e(formatDate($invoice['invoice_date']))
                                    : '-' ?>
                            </td>

                            <td>
                                <?= formatMoney($invoice['amount'] ?? 0) ?>
                            </td>

                            <td>
                                <?= e($invoice['status'] ?? '-') ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="5" class="text-center">
                            Aucune facture disponible pour le moment.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>
