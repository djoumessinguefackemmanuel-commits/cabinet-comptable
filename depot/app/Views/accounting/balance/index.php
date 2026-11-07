<?php
$balances = $balances ?? [];

$totalDebit = 0;
$totalCredit = 0;
$totalDebitBalance = 0;
$totalCreditBalance = 0;

foreach ($balances as $item) {
    $totalDebit += (float) ($item['total_debit'] ?? 0);
    $totalCredit += (float) ($item['total_credit'] ?? 0);
    $totalDebitBalance += (float) ($item['debit_balance'] ?? 0);
    $totalCreditBalance += (float) ($item['credit_balance'] ?? 0);
}

?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1">Balance comptable</h1>
            <p class="text-muted mb-0">
                Consultation des mouvements et des soldes des comptes.
            </p>
        </div>

    </div>

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Filtres de consultation</h5>
        </div>

        <div class="card-body">

            <form action="<?= url('accounting/balance') ?>" method="GET">

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
                            value="<?= e($_GET['start_date'] ?? '') ?>"
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
                            value="<?= e($_GET['end_date'] ?? '') ?>"
                        >

                    </div>

                    <div class="col-md-4 mb-3">

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>
                            Filtrer
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

    <div class="row mb-4">

        <div class="col-md-6 col-xl-3 mb-3">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <p class="text-muted mb-1">Total des débits</p>

                    <h5 class="text-primary mb-0">
                        <?= number_format($totalDebit, 2, ',', ' ') ?> FCFA
                    </h5>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-xl-3 mb-3">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <p class="text-muted mb-1">Total des crédits</p>

                    <h5 class="text-success mb-0">
                        <?= number_format($totalCredit, 2, ',', ' ') ?> FCFA
                    </h5>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-xl-3 mb-3">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <p class="text-muted mb-1">Soldes débiteurs</p>

                    <h5 class="text-primary mb-0">
                        <?= number_format($totalDebitBalance, 2, ',', ' ') ?> FCFA
                    </h5>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-xl-3 mb-3">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <p class="text-muted mb-1">Soldes créditeurs</p>

                    <h5 class="text-success mb-0">
                        <?= number_format($totalCreditBalance, 2, ',', ' ') ?> FCFA
                    </h5>

                </div>

            </div>

        </div>

    </div>

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white py-3">

            <h5 class="mb-0">
                Détail de la balance
            </h5>

        </div>

        <div class="card-body">

            <?php if (!empty($balances)): ?>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-light">

                            <tr>
                                <th rowspan="2" class="align-middle">N° de compte</th>
                                <th rowspan="2" class="align-middle">Intitulé du compte</th>
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

                            <?php foreach ($balances as $item): ?>

                                <tr>

                                    <td>
                                        <?= e($item['account_number'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?= e($item['account_name'] ?? '') ?>
                                    </td>

                                    <td class="text-end">
                                        <?= number_format((float) ($item['total_debit'] ?? 0), 2, ',', ' ') ?>
                                    </td>

                                    <td class="text-end">
                                        <?= number_format((float) ($item['total_credit'] ?? 0), 2, ',', ' ') ?>
                                    </td>

                                    <td class="text-end">
                                        <?= number_format((float) ($item['debit_balance'] ?? 0), 2, ',', ' ') ?>
                                    </td>

                                    <td class="text-end">
                                        <?= number_format((float) ($item['credit_balance'] ?? 0), 2, ',', ' ') ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                        <tfoot class="table-light">

                            <tr>

                                <th colspan="2" class="text-end">
                                    Totaux
                                </th>

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

                <?php if (abs($totalDebit - $totalCredit) < 0.01): ?>

                    <div class="alert alert-success mt-3 mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        La balance présente des mouvements équilibrés.
                    </div>

                <?php else: ?>

                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Attention : les mouvements débiteurs et créditeurs présentent un écart de
                        <?= number_format(abs($totalDebit - $totalCredit), 2, ',', ' ') ?>
                        FCFA.
                    </div>

                <?php endif; ?>

            <?php else: ?>

                <div class="text-center py-5">

                    <i class="fas fa-balance-scale fa-3x text-muted mb-3"></i>

                    <h5>Aucune donnée disponible</h5>

                    <p class="text-muted mb-0">
                        Aucun mouvement comptable ne correspond aux critères sélectionnés.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php
?>