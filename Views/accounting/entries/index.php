<?php
?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1">Écritures comptables</h1>
            <p class="text-muted mb-0">
                Consultation et gestion des écritures comptables.
            </p>
        </div>

        <a href="<?= url('accounting/entries/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>
            Nouvelle écriture
        </a>

    </div>

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Liste des écritures</h5>
        </div>

        <div class="card-body">

            <?php if (!empty($entries)): ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Référence</th>
                                <th>Date</th>
                                <th>Journal</th>
                                <th>Description</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($entries as $entry): ?>

                                <tr>

                                    <td>
                                        <?= e($entry['id']) ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?= e($entry['reference'] ?? $entry['id']) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= formatDate($entry['entry_date']) ?>
                                    </td>

                                    <td>
                                        <?= e($entry['journal_name'] ?? 'Non renseigné') ?>
                                    </td>

                                    <td>
                                        <?= e($entry['description']) ?>
                                    </td>

                                    <td>

                                        <?php
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

                                        $status = $entry['status'];
                                        ?>

                                        <span class="badge <?= $statusClasses[$status] ?? 'bg-secondary' ?>">
                                            <?= e($statusLabels[$status] ?? $status) ?>
                                        </span>

                                    </td>

                                    <td class="text-end">

                                        <a
                                            href="<?= url('accounting/entries/' . $entry['id']) ?>"
                                            class="btn btn-sm btn-outline-info"
                                            title="Consulter"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </a>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="text-center py-5">

                    <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>

                    <h5>Aucune écriture comptable</h5>

                    <p class="text-muted">
                        Aucune écriture comptable n'a encore été enregistrée.
                    </p>

                    <a
                        href="<?= url('accounting/entries/create') ?>"
                        class="btn btn-primary"
                    >
                        <i class="fas fa-plus me-2"></i>
                        Créer une écriture
                    </a>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php
?>