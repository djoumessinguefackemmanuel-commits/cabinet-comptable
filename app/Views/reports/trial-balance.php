<?php


$accounts = $accounts ?? [];

$totalDebit = (float) ($report['total_debit'] ?? 0);
$totalCredit = (float) ($report['total_credit'] ?? 0);
$totalDebitBalance = (float) ($report['total_debit_balance'] ?? 0);
$totalCreditBalance = (float) ($report['total_credit_balance'] ?? 0);

$startDate = $report['start_date'] ?? '';
$endDate = $report['end_date'] ?? '';
?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1">Balance générale</h1>
            <p class="text-muted mb-0">
                Consultation des mouvements et soldes des comptes comptables.
            </p>
        </div>

        <a href="<?= url('reports') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Retour
        </a>

    </div>

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <form action="<?= url('reports/trial-balance') ?>" method="GET">

                <div class="row align-items-end">

                    <div class="col-md-4 mb-3">

                        <label for="start_date" class="form-label">
                            Date de début
                        </label>

                        <input
                            type="date"
                            name="start_date"
                            id="start_date"
                            class="form-control"
                            value="<?= e($_GET['start_date'] ?? $startDate) ?>"
                        >

                    </div>

                    <div class="col-md-4 mb-3">

                        <label for="end_date" class="form-label">
                            Date de fin
                        </label>

                        <input
                            type="date"
                            name="end_date"
                            id="end_date"
                            class="form-control"
                            value="<?= e($_GET['end_date'] ?? $endDate) ?>"
                        >

                    </div>

                    <div class="col-md-4 mb-3">

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>
                            Générer la balance
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white py-3">

            <div class="text-center">

                <h4 class="mb-1">BALANCE GÉNÉRALE DES COMPTES</h4>

                <p class="text-muted mb-0">
                    Période du
                    <?= $startDate ? formatDate($startDate) : 'Non définie' ?>
                    au
                    <?= $endDate ? formatDate($endDate) : 'Non définie' ?>
                </p>

            </div>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">

                        <tr>
                            <th rowspan="2">N° de compte</th>
                            <th rowspan="2">Intitulé du compte</th>
                            <th colspan="2" class="text-center">Mouvements</th>
                            <th colspan="2" class="text-center">Soldes</th>
                        </tr>

                        <tr>
                            <th class="text-end">Débit</th>
                            <th class="text-end">Crédit</th>
                            <th class="text-end">Débiteur</th>
                            <th class="text-end">Créditeur</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php if (!empty($accounts)): ?>

                            <?php foreach ($accounts as $account): ?>

                                <tr>

                                    <td>
                                        <?= e($account['account_number'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?= e($account['account_name'] ?? '') ?>
                                    </td>

                                    <td class="text-end">
                                        <?= number_format(
                                            (float) ($account['total_debit'] ?? 0),
                                            2,
                                            ',',
                                            ' '
                                        ) ?>
                                    </td>

                                    <td class="text-end">
                                        <?= number_format(
                                            (float) ($account['total_credit'] ?? 0),
                                            2,
                                            ',',
                                            ' '
                                        ) ?>
                                    </td>

                                    <td class="text-end">
                                        <?= number_format(
                                            (float) ($account['debit_balance'] ?? 0),
                                            2,
                                            ',',
                                            ' '
                                        ) ?>
                                    </td>

                                    <td class="text-end">
                                        <?= number_format(
                                            (float) ($account['credit_balance'] ?? 0),
                                            2,
                                            ',',
                                            ' '
                                        ) ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Aucune donnée comptable disponible pour cette période.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                    <tfoot class="table-light">

                        <tr class="fw-bold">

                            <th colspan="2">TOTAUX</th>

                            <th class="text-end">
                                <?= number_format($totalDebit, 2, ',', ' ') ?>
                            </th>

                            <th class="text-end">
                                <?= number_format($totalCredit, 2, ',', ' ') ?>
                            </th>

                            <th class="text-end">
                                <?= number_format($totalDebitBalance, 2, ',', ' ') ?>
                            </th>

                            <th class="text-end">
                                <?= number_format($totalCreditBalance, 2, ',', ' ') ?>
                            </th>

                        </tr>

                    </tfoot>

                </table>

            </div>

            <div class="mt-3">

                <div class="d-flex justify-content-between align-items-center">

                    <span class="text-muted">
                        Contrôle de l'équilibre des mouvements
                    </span>

                    <?php if (abs($totalDebit - $totalCredit) < 0.01): ?>

                        <span class="badge bg-success">
                            <i class="fas fa-check-circle me-1"></i>
                            Balance équilibrée
                        </span>

                    <?php else: ?>

                        <span class="badge bg-danger">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Écart constaté
                        </span>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

</div>

<?php
?>