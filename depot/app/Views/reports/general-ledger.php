<?php


$accounts = $accounts ?? [];
$selectedAccount = $selectedAccount ?? null;
$entries = $entries ?? [];

$startDate = $report['start_date'] ?? '';
$endDate = $report['end_date'] ?? '';

$totalDebit = (float) ($report['total_debit'] ?? 0);
$totalCredit = (float) ($report['total_credit'] ?? 0);
$finalBalance = (float) ($report['final_balance'] ?? 0);
?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1">Grand livre comptable</h1>
            <p class="text-muted mb-0">
                Consultation détaillée des opérations par compte.
            </p>
        </div>

        <a href="<?= url('reports') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Retour
        </a>

    </div>

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <form action="<?= url('reports/general-ledger') ?>" method="GET">

                <div class="row align-items-end">

                    <div class="col-md-3 mb-3">

                        <label for="account_id" class="form-label">
                            Compte comptable
                        </label>

                        <select
                            name="account_id"
                            id="account_id"
                            class="form-select"
                            required
                        >

                            <option value="">Sélectionner un compte</option>

                            <?php foreach ($accounts as $account): ?>

                                <option
                                    value="<?= (int) $account['id'] ?>"
                                    <?= (int) ($_GET['account_id'] ?? 0) === (int) $account['id'] ? 'selected' : '' ?>
                                >
                                    <?= e($account['account_number'] ?? '') ?>
                                    -
                                    <?= e($account['name'] ?? '') ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="col-md-3 mb-3">

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

                    <div class="col-md-3 mb-3">

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

                    <div class="col-md-3 mb-3">

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>
                            Consulter
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

    <?php if ($selectedAccount): ?>

        <div class="card shadow-sm border-0 mb-4">

            <div class="card-body">

                <div class="row align-items-center">

                    <div class="col-md-8">

                        <span class="text-muted small">
                            COMPTE SÉLECTIONNÉ
                        </span>

                        <h5 class="mb-1">
                            <?= e($selectedAccount['account_number'] ?? '') ?>
                            -
                            <?= e($selectedAccount['name'] ?? '') ?>
                        </h5>

                    </div>

                    <div class="col-md-4 text-md-end">

                        <span class="text-muted small">
                            SOLDE FINAL
                        </span>

                        <h4 class="mb-0 <?= $finalBalance >= 0 ? 'text-success' : 'text-danger' ?>">
                            <?= number_format($finalBalance, 2, ',', ' ') ?> FCFA
                        </h4>

                    </div>

                </div>

            </div>

        </div>

        <div class="card shadow-sm border-0">

            <div class="card-header bg-white py-3">

                <h5 class="mb-0">Détail des mouvements</h5>

                <small class="text-muted">
                    Période du
                    <?= $startDate ? formatDate($startDate) : 'Non définie' ?>
                    au
                    <?= $endDate ? formatDate($endDate) : 'Non définie' ?>
                </small>

            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-light">

                            <tr>
                                <th>Date</th>
                                <th>Référence</th>
                                <th>Journal</th>
                                <th>Libellé</th>
                                <th class="text-end">Débit</th>
                                <th class="text-end">Crédit</th>
                                <th class="text-end">Solde</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php if (!empty($entries)): ?>

                                <?php foreach ($entries as $entry): ?>

                                    <tr>

                                        <td>
                                            <?= !empty($entry['entry_date']) ? formatDate($entry['entry_date']) : '-' ?>
                                        </td>

                                        <td>
                                            <?= e($entry['reference'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <?= e($entry['journal_name'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <?= e($entry['description'] ?? '-') ?>
                                        </td>

                                        <td class="text-end">
                                            <?= number_format(
                                                (float) ($entry['debit'] ?? 0),
                                                2,
                                                ',',
                                                ' '
                                            ) ?>
                                        </td>

                                        <td class="text-end">
                                            <?= number_format(
                                                (float) ($entry['credit'] ?? 0),
                                                2,
                                                ',',
                                                ' '
                                            ) ?>
                                        </td>

                                        <td class="text-end fw-bold">
                                            <?= number_format(
                                                (float) ($entry['balance'] ?? 0),
                                                2,
                                                ',',
                                                ' '
                                            ) ?>
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Aucune opération trouvée pour ce compte.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                        <tfoot class="table-light">

                            <tr class="fw-bold">

                                <th colspan="4">TOTAUX</th>

                                <th class="text-end">
                                    <?= number_format($totalDebit, 2, ',', ' ') ?>
                                </th>

                                <th class="text-end">
                                    <?= number_format($totalCredit, 2, ',', ' ') ?>
                                </th>

                                <th class="text-end">
                                    <?= number_format($finalBalance, 2, ',', ' ') ?>
                                </th>

                            </tr>

                        </tfoot>

                    </table>

                </div>

            </div>

        </div>

    <?php else: ?>

        <div class="card shadow-sm border-0">

            <div class="card-body text-center py-5">

                <i class="fas fa-book-open fa-3x text-muted mb-3"></i>

                <h5>Aucun compte sélectionné</h5>

                <p class="text-muted mb-0">
                    Sélectionne un compte comptable et une période pour consulter son grand livre.
                </p>

            </div>

        </div>

    <?php endif; ?>

</div>

<?php
?>