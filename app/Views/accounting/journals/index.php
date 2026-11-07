<?php
?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1">Journaux comptables</h1>
            <p class="text-muted mb-0">
                Consultation et gestion des journaux comptables.
            </p>
        </div>

        <a href="<?= url('accounting/journals/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>
            Nouveau journal
        </a>

    </div>

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Liste des journaux</h5>
        </div>

        <div class="card-body">

            <?php if (!empty($journals)): ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-light">

                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th>Nom du journal</th>
                                <th>Type</th>
                                <th>Statut</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($journals as $journal): ?>

                                <tr>

                                    <td>
                                        <?= e($journal['id']) ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?= e($journal['code']) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= e($journal['name']) ?>
                                    </td>

                                    <td>
                                        <?= e($journal['type']) ?>
                                    </td>

                                    <td>

                                        <?php if (!empty($journal['is_active'])): ?>

                                            <span class="badge bg-success">
                                                Actif
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-secondary">
                                                Inactif
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="text-center py-5">

                    <i class="fas fa-book-open fa-3x text-muted mb-3"></i>

                    <h5>Aucun journal comptable</h5>

                    <p class="text-muted">
                        Aucun journal n'a encore été enregistré.
                    </p>

                    <a
                        href="<?= url('accounting/journals/create') ?>"
                        class="btn btn-primary"
                    >
                        <i class="fas fa-plus me-2"></i>
                        Ajouter un journal
                    </a>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php
?>