<?php
$entries = $entries ?? [];
$accounts = $accounts ?? [];
$selectedAccount = $selectedAccount ?? null;

$totalDebit = 0;
$totalCredit = 0;
$balance = 0;

foreach ($entries as $entry) {
    $totalDebit += (float) ($entry['debit'] ?? 0);
    $totalCredit += (float) ($entry['credit'] ?? 0);
}

$balance = $totalDebit - $totalCredit;
?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1">Grand livre comptable</h1>
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

            <form action="<?= url('accounting/ledger') ?>" method="GET">

                <div class="row align-items-end">

                    <div class="col-md-4 mb-3">

                        <label for="account_id" class="form-label">
                            Compte comptable
                        </label>

                        <select
                            name="account_id"
                            id="account_id"
                            class="form-select"
                        >

                            <option value="">Tous les comptes</option>

                            <?php foreach ($accounts as $account): ?>

                                <option
                                    value="<?= e($account['id']) ?>"
                                    <?= (string) ($_GET['account_id'] ?? '') === (string) $account['id'] ? 'selected' : '' ?>
                                >
                                    <?= e($account['account_number']) ?>
                                    - <?= e($account['name']) ?>
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
                            value="<?= e($_GET['start_date'] ?? '') ?>"
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
                            value="<?= e($_GET['end_date'] ?? '') ?>"
                        >

                    </div>

                    <div class="col-md-2 mb-3">

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

        <div class="col-md-4 mb-3">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <p class="text-muted mb-1">Total débit</p>

                    <h4 class="mb-0 text-primary">
                        <?= number_format($totalDebit, 2, ',', ' ') ?> FCFA
                    </h4>

                </div>

            </div>

        </div>

        <div class="col-md-4 mb-3">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <p class="text-muted mb-1">Total crédit</p>

                    <h4 class="mb-0 text-success">
                        <?= number_format($totalCredit, 2, ',', ' ') ?> FCFA
                    </h4>

                </div>

            </div>

        </div>

        <div class="col-md-4 mb-3">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <p class="text-muted mb-1">Solde débiteur / créditeur</p>

                    <h4 class="mb-0 <?= $balance >= 0 ? 'text-primary' : 'text-danger' ?>">
                        <?= number_format(abs($balance), 2, ',', ' ') ?> FCFA
                    </h4>

                    <small class="text-muted">
                        <?= $balance > 0 ? 'Solde débiteur' : ($balance < 0 ? 'Solde créditeur' : 'Solde nul') ?>
                    </small>

                </div>

            </div>

        </div>

    </div>

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white py-3">

            <h5 class="mb-0">
                Mouvements comptables
                <?php if ($selectedAccount): ?>
                    - <?= e($selectedAccount['account_number'] ?? '') ?>
                    <?= e($selectedAccount['name'] ?? '') ?>
                <?php endif; ?>
            </h5>

        </div>

        <div class="card-body">

            <?php if (!empty($entries)): ?>

                <div class="table-responsive">

                    <table class="table table-hover table-bordered align-middle">

                        <thead class="table-light">

                            <tr>
                                <th>Date</th>
                                <th>Journal</th>
                                <th>Référence</th>
                                <th>Compte</th>
                                <th>Libellé</th>
                                <th class="text-end">Débit (FCFA)</th>
                                <th class="text-end">Crédit (FCFA)</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($entries as $entry): ?>

                                <tr>

                                    <td>
                                        <?= formatDate($entry['entry_date'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?= e($entry['journal_name'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?= e($entry['reference'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?= e($entry['account_number'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?= e($entry['description'] ?? '') ?>
                                    </td>

                                    <td class="text-end">
                                        <?= number_format((float) ($entry['debit'] ?? 0), 2, ',', ' ') ?>
                                    </td>

                                    <td class="text-end">
                                        <?= number_format((float) ($entry['credit'] ?? 0), 2, ',', ' ') ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                        <tfoot class="table-light">

                            <tr>

                                <th colspan="5" class="text-end">
                                    Totaux
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

            <?php else: ?>

                <div class="text-center py-5">

                    <i class="fas fa-book-open fa-3x text-muted mb-3"></i>

                    <h5>Aucun mouvement comptable</h5>

                    <p class="text-muted mb-0">
                        Aucun mouvement ne correspond aux critères de consultation.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php
?>