<?php


$report = $report ?? [];

$revenues = $report['revenues'] ?? [];
$expenses = $report['expenses'] ?? [];

$totalRevenues = (float) ($report['total_revenues'] ?? 0);
$totalExpenses = (float) ($report['total_expenses'] ?? 0);
$netResult = (float) ($report['net_result'] ?? ($totalRevenues - $totalExpenses));

$startDate = $report['start_date'] ?? '';
$endDate = $report['end_date'] ?? '';
?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1">Compte de résultat</h1>
            <p class="text-muted mb-0">
                Présentation des produits, des charges et du résultat de l'entreprise.
            </p>
        </div>

        <a href="<?= url('reports') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Retour
        </a>

    </div>

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <form action="<?= url('reports/income-statement') ?>" method="GET">

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
                            Générer le rapport
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white py-3">

            <div class="text-center">

                <h4 class="mb-1">COMPTE DE RÉSULTAT</h4>

                <p class="text-muted mb-0">
                    Période du
                    <?= $startDate ? formatDate($startDate) : 'Non définie' ?>
                    au
                    <?= $endDate ? formatDate($endDate) : 'Non définie' ?>
                </p>

            </div>

        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-6 mb-4">

                    <div class="card border h-100">

                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">PRODUITS</h5>
                        </div>

                        <div class="card-body p-0">

                            <?php if (!empty($revenues)): ?>

                                <div class="table-responsive">

                                    <table class="table table-bordered mb-0">

                                        <thead class="table-light">

                                            <tr>
                                                <th>Compte</th>
                                                <th>Intitulé</th>
                                                <th class="text-end">Montant (FCFA)</th>
                                            </tr>

                                        </thead>

                                        <tbody>

                                            <?php foreach ($revenues as $revenue): ?>

                                                <tr>

                                                    <td>
                                                        <?= e($revenue['account_number'] ?? '') ?>
                                                    </td>

                                                    <td>
                                                        <?= e($revenue['account_name'] ?? '') ?>
                                                    </td>

                                                    <td class="text-end">
                                                        <?= number_format(
                                                            (float) ($revenue['amount'] ?? 0),
                                                            2,
                                                            ',',
                                                            ' '
                                                        ) ?>
                                                    </td>

                                                </tr>

                                            <?php endforeach; ?>

                                        </tbody>

                                        <tfoot class="table-light">

                                            <tr>
                                                <th colspan="2">TOTAL PRODUITS</th>
                                                <th class="text-end">
                                                    <?= number_format($totalRevenues, 2, ',', ' ') ?>
                                                </th>
                                            </tr>

                                        </tfoot>

                                    </table>

                                </div>

                            <?php else: ?>

                                <p class="text-muted text-center py-4 mb-0">
                                    Aucun produit disponible.
                                </p>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

                <div class="col-md-6 mb-4">

                    <div class="card border h-100">

                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">CHARGES</h5>
                        </div>

                        <div class="card-body p-0">

                            <?php if (!empty($expenses)): ?>

                                <div class="table-responsive">

                                    <table class="table table-bordered mb-0">

                                        <thead class="table-light">

                                            <tr>
                                                <th>Compte</th>
                                                <th>Intitulé</th>
                                                <th class="text-end">Montant (FCFA)</th>
                                            </tr>

                                        </thead>

                                        <tbody>

                                            <?php foreach ($expenses as $expense): ?>

                                                <tr>

                                                    <td>
                                                        <?= e($expense['account_number'] ?? '') ?>
                                                    </td>

                                                    <td>
                                                        <?= e($expense['account_name'] ?? '') ?>
                                                    </td>

                                                    <td class="text-end">
                                                        <?= number_format(
                                                            (float) ($expense['amount'] ?? 0),
                                                            2,
                                                            ',',
                                                            ' '
                                                        ) ?>
                                                    </td>

                                                </tr>

                                            <?php endforeach; ?>

                                        </tbody>

                                        <tfoot class="table-light">

                                            <tr>
                                                <th colspan="2">TOTAL CHARGES</th>
                                                <th class="text-end">
                                                    <?= number_format($totalExpenses, 2, ',', ' ') ?>
                                                </th>
                                            </tr>

                                        </tfoot>

                                    </table>

                                </div>

                            <?php else: ?>

                                <p class="text-muted text-center py-4 mb-0">
                                    Aucune charge disponible.
                                </p>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </div>

            <div class="card border-0 bg-light">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <span>Total des produits</span>

                        <strong>
                            <?= number_format($totalRevenues, 2, ',', ' ') ?> FCFA
                        </strong>

                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <span>Total des charges</span>

                        <strong>
                            <?= number_format($totalExpenses, 2, ',', ' ') ?> FCFA
                        </strong>

                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center">

                        <strong>RÉSULTAT NET</strong>

                        <h4 class="<?= $netResult >= 0 ? 'text-success' : 'text-danger' ?> mb-0">

                            <?= number_format($netResult, 2, ',', ' ') ?> FCFA

                        </h4>

                    </div>

                    <div class="text-end mt-2">

                        <span class="badge <?= $netResult >= 0 ? 'bg-success' : 'bg-danger' ?>">

                            <?= $netResult > 0 ? 'Bénéfice' : ($netResult < 0 ? 'Perte' : 'Résultat nul') ?>

                        </span>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<?php
?>