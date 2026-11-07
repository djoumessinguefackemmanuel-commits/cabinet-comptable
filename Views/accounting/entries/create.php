<?php
$formOld = $old ?? [];
$oldLines = $formOld['lines'] ?? old('lines', []);

if (!is_array($oldLines) || empty($oldLines)) {
    $oldLines = [
        ['account_id' => '', 'description' => '', 'debit' => '', 'credit' => ''],
        ['account_id' => '', 'description' => '', 'debit' => '', 'credit' => '']
    ];
}
?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1">Nouvelle écriture comptable</h1>
            <p class="text-muted mb-0">
                Saisir une opération dans un journal comptable.
            </p>
        </div>

        <a href="<?= url('accounting/entries') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Retour à la liste
        </a>

    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= url('accounting/entries') ?>" method="POST">

        <?= csrfField() ?>

        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Informations générales</h5>
            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label for="journal_id" class="form-label">
                            Journal comptable <span class="text-danger">*</span>
                        </label>

                        <select
                            name="journal_id"
                            id="journal_id"
                            class="form-select"
                            required
                        >

                            <option value="">Sélectionner un journal</option>

                            <?php foreach (($journals ?? []) as $journal): ?>

                                <option
                                    value="<?= e($journal['id']) ?>"
                                    <?= (string) ($formOld['journal_id'] ?? old('journal_id')) === (string) $journal['id'] ? 'selected' : '' ?>
                                >
                                    <?= e($journal['code'] . ' - ' . $journal['name']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label for="exercise_id" class="form-label">
                            Exercice comptable <span class="text-danger">*</span>
                        </label>

                        <select
                            name="exercise_id"
                            id="exercise_id"
                            class="form-select"
                            required
                        >
                            <option value="">Sélectionner un exercice</option>
                            <?php foreach (($exercises ?? []) as $exercise): ?>
                                <option
                                    value="<?= e($exercise['id']) ?>"
                                    <?= (string) ($formOld['exercise_id'] ?? old('exercise_id')) === (string) $exercise['id'] ? 'selected' : '' ?>
                                >
                                    <?= e($exercise['company_name'] . ' - ' . $exercise['name']) ?>
                                    (<?= e($exercise['start_date']) ?> — <?= e($exercise['end_date']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label for="entry_date" class="form-label">
                            Date de l'écriture <span class="text-danger">*</span>
                        </label>

                        <input
                            type="date"
                            name="entry_date"
                            id="entry_date"
                            class="form-control"
                            value="<?= e($formOld['entry_date'] ?? old('entry_date', date('Y-m-d'))) ?>"
                            required
                        >

                    </div>

                    <div class="col-12 mb-3">

                        <label for="description" class="form-label">
                            Description <span class="text-danger">*</span>
                        </label>

                        <textarea
                            name="description"
                            id="description"
                            class="form-control"
                            rows="3"
                            placeholder="Décrire l'opération comptable..."
                            required
                        ><?= e($formOld['description'] ?? old('description')) ?></textarea>

                    </div>

                </div>

            </div>

        </div>

        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Lignes comptables</h5>
            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered align-middle">

                        <thead class="table-light">

                            <tr>
                                <th style="min-width: 190px;">Compte</th>
                                <th style="min-width: 180px;">Description</th>
                                <th style="min-width: 130px;">Débit (FCFA)</th>
                                <th style="min-width: 130px;">Crédit (FCFA)</th>
                                <th style="width: 60px;">Action</th>
                            </tr>

                        </thead>

                        <tbody id="entry-lines">

                            <?php foreach ($oldLines as $index => $line): ?>

                                <tr class="entry-line">

                                    <td>

                                        <select
                                            name="lines[<?= $index ?>][account_id]"
                                            class="form-select"
                                            required
                                        >

                                            <option value="">Choisir un compte</option>

                                            <?php foreach (($accounts ?? []) as $account): ?>

                                                <option
                                                    value="<?= e($account['id']) ?>"
                                                    <?= (string) ($line['account_id'] ?? '') === (string) $account['id'] ? 'selected' : '' ?>
                                                >
                                                    <?= e($account['account_number'] . ' - ' . $account['name']) ?>
                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                    </td>

                                    <td>

                                        <input
                                            type="text"
                                            name="lines[<?= $index ?>][description]"
                                            class="form-control"
                                            placeholder="Libellé"
                                            value="<?= e($line['description'] ?? '') ?>"
                                        >

                                    </td>

                                    <td>

                                        <input
                                            type="number"
                                            name="lines[<?= $index ?>][debit]"
                                            class="form-control debit-input"
                                            min="0"
                                            step="0.01"
                                            value="<?= e($line['debit'] ?? '') ?>"
                                        >

                                    </td>

                                    <td>

                                        <input
                                            type="number"
                                            name="lines[<?= $index ?>][credit]"
                                            class="form-control credit-input"
                                            min="0"
                                            step="0.01"
                                            value="<?= e($line['credit'] ?? '') ?>"
                                        >

                                    </td>

                                    <td class="text-center">

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger remove-line"
                                            title="Supprimer cette ligne"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                        <tfoot class="table-light">

                            <tr>

                                <th colspan="2" class="text-end">
                                    Totaux
                                </th>

                                <th>
                                    <span id="total-debit">0.00</span>
                                </th>

                                <th>
                                    <span id="total-credit">0.00</span>
                                </th>

                                <th></th>

                            </tr>

                            <tr>

                                <th colspan="2" class="text-end">
                                    Écart
                                </th>

                                <th colspan="2">
                                    <span id="balance-difference">0.00 FCFA</span>
                                </th>

                                <th></th>

                            </tr>

                        </tfoot>

                    </table>

                </div>

                <button type="button" id="add-line" class="btn btn-outline-primary">
                    <i class="fas fa-plus me-2"></i>
                    Ajouter une ligne
                </button>

            </div>

        </div>

        <div class="d-flex justify-content-end gap-2 mb-4">

            <a href="<?= url('accounting/entries') ?>" class="btn btn-light">
                Annuler
            </a>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>
                Enregistrer l'écriture
            </button>

        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const tableBody = document.getElementById('entry-lines');
    const addButton = document.getElementById('add-line');

    function updateTotals() {

        let totalDebit = 0;
        let totalCredit = 0;

        document.querySelectorAll('.debit-input').forEach(function (input) {
            totalDebit += parseFloat(input.value) || 0;
        });

        document.querySelectorAll('.credit-input').forEach(function (input) {
            totalCredit += parseFloat(input.value) || 0;
        });

        document.getElementById('total-debit').textContent = totalDebit.toFixed(2);
        document.getElementById('total-credit').textContent = totalCredit.toFixed(2);

        const difference = Math.abs(totalDebit - totalCredit);

        document.getElementById('balance-difference').textContent =
            difference.toFixed(2) + ' FCFA';
    }

    function updateIndexes() {

        const rows = tableBody.querySelectorAll('.entry-line');

        rows.forEach(function (row, index) {

            row.querySelectorAll('select, input').forEach(function (field) {

                field.name = field.name.replace(
                    /lines\[\d+\]/,
                    'lines[' + index + ']'
                );

            });

        });

    }

    addButton.addEventListener('click', function () {

        const index = tableBody.querySelectorAll('.entry-line').length;

        const row = document.createElement('tr');

        row.className = 'entry-line';

        row.innerHTML = `
            <td>
                <select name="lines[${index}][account_id]" class="form-select" required>
                    <option value="">Choisir un compte</option>

                    <?php foreach (($accounts ?? []) as $account): ?>
                        <option value="<?= e($account['id']) ?>">
                            <?= e($account['account_number'] . ' - ' . $account['name']) ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </td>

            <td>
                <input
                    type="text"
                    name="lines[${index}][description]"
                    class="form-control"
                    placeholder="Libellé"
                >
            </td>

            <td>
                <input
                    type="number"
                    name="lines[${index}][debit]"
                    class="form-control debit-input"
                    min="0"
                    step="0.01"
                >
            </td>

            <td>
                <input
                    type="number"
                    name="lines[${index}][credit]"
                    class="form-control credit-input"
                    min="0"
                    step="0.01"
                >
            </td>

            <td class="text-center">
                <button
                    type="button"
                    class="btn btn-sm btn-outline-danger remove-line"
                    title="Supprimer cette ligne"
                >
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;

        tableBody.appendChild(row);

    });

    tableBody.addEventListener('input', function (event) {

        if (
            event.target.classList.contains('debit-input') ||
            event.target.classList.contains('credit-input')
        ) {
            updateTotals();
        }

    });

    tableBody.addEventListener('click', function (event) {

        const removeButton = event.target.closest('.remove-line');

        if (removeButton) {

            const rows = tableBody.querySelectorAll('.entry-line');

            if (rows.length <= 2) {
                alert('Une écriture comptable doit contenir au moins deux lignes.');
                return;
            }

            removeButton.closest('.entry-line').remove();

            updateIndexes();
            updateTotals();

        }

    });

    updateTotals();

});
</script>

<?php
?>