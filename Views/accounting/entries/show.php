<?php
$totalDebit = 0;
$totalCredit = 0;

$lines = $lines ?? [];

foreach ($lines as $line) {
    $totalDebit += (float) ($line['debit'] ?? 0);
    $totalCredit += (float) ($line['credit'] ?? 0);
}

$status = $entry['status'] ?? '';

$statusLabels = [
    ENTRY_DRAFT => 'Brouillon',
    ENTRY_VALIDATED => 'Validée',
    ENTRY_CANCELLED => 'Annulée'
];

$statusClasses = [
    ENTRY_DRAFT => 'bg-warning text-dark',
    ENTRY_VALIDATED => 'bg-success',
    ENTRY_CANCELLED => 'bg-danger'
];
?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1">Détails de l'écriture comptable</h1>
            <p class="text-muted mb-0">
                Consultation des informations de l'écriture.
            </p>
        </div>

        <a href="<?= url('accounting/entries') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Retour à la liste
        </a>

    </div>

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Informations générales</h5>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label class="text-muted small">Référence</label>

                    <p class="fw-semibold mb-0">
                        <?= e($entry['reference'] ?? $entry['id']) ?>
                    </p>

                </div>

                <div class="col-md-6 mb-3">

                    <label class="text-muted small">Journal comptable</label>

                    <p class="fw-semibold mb-0">
                        <?= e($entry['journal_name'] ?? 'Non renseigné') ?>
                    </p>

                </div>

                <div class="col-md-6 mb-3">

                    <label class="text-muted small">Date de l'écriture</label>

                    <p class="fw-semibold mb-0">
                        <?= formatDate($entry['entry_date']) ?>
                    </p>

                </div>

                <div class="col-md-6 mb-3">

                    <label class="text-muted small">Statut</label>

                    <p class="mb-0">
                        <span class="badge <?= $statusClasses[$status] ?? 'bg-secondary' ?>">
                            <?= e($statusLabels[$status] ?? $status) ?>
                        </span>
                    </p>

                </div>

                <div class="col-12 mb-3">

                    <label class="text-muted small">Description</label>

                    <p class="mb-0">
                        <?= e($entry['description']) ?>
                    </p>

                </div>

            </div>

        </div>

    </div>

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Détail des lignes comptables</h5>
        </div>

        <div class="card-body">

            <?php if (!empty($lines)): ?>

                <div class="table-responsive">

                    <table class="table table-bordered align-middle">

                        <thead class="table-light">

                            <tr>
                                <th>Compte</th>
                                <th>Intitulé du compte</th>
                                <th>Description</th>
                                <th class="text-end">Débit (FCFA)</th>
                                <th class="text-end">Crédit (FCFA)</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($lines as $line): ?>

                                <tr>

                                    <td>
                                        <?= e($line['account_number'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?= e($line['account_name'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?= e($line['description'] ?? '') ?>
                                    </td>

                                    <td class="text-end">
                                        <?= number_format((float) ($line['debit'] ?? 0), 2, ',', ' ') ?>
                                    </td>

                                    <td class="text-end">
                                        <?= number_format((float) ($line['credit'] ?? 0), 2, ',', ' ') ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                        <tfoot class="table-light">

                            <tr>

                                <th colspan="3" class="text-end">
                                    Total
                                </th>

                                <th class="text-end">
                                    <?= number_format($totalDebit, 2, ',', ' ') ?>
                                </th>

                                <th class="text-end">
                                    <?= number_format($totalCredit, 2, ',', ' ') ?>
                                </th>

                            </tr>

                        </tfoot>

                    </table>

                </div>

                <div class="mt-3">

                    <?php if (abs($totalDebit - $totalCredit) < 0.01): ?>

                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle me-2"></i>
                            L'écriture comptable est équilibrée.
                        </div>

                    <?php else: ?>

                        <div class="alert alert-danger mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            L'écriture comptable présente un déséquilibre de
                            <?= number_format(abs($totalDebit - $totalCredit), 2, ',', ' ') ?>
                            FCFA.
                        </div>

                    <?php endif; ?>

                </div>

            <?php else: ?>

                <div class="text-center py-4">

                    <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>

                    <h5>Aucune ligne comptable</h5>

                    <p class="text-muted mb-0">
                        Cette écriture ne contient aucune ligne comptable.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php
?>