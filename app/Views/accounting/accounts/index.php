<?php
?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1">Plan comptable</h1>
            <p class="text-muted mb-0">
                Consultation et gestion des comptes comptables.
            </p>
        </div>

        <a href="<?= url('accounting/accounts/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>
            Nouveau compte
        </a>

    </div>

    <?php if (!empty($companies)): ?>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="POST" action="<?= url('accounting/accounts/import-syscohada') ?>" class="row g-2 align-items-end"
                  onsubmit="return confirm('Importer le plan comptable SYSCOHADA dans cette entreprise ? Les comptes déjà présents sont conservés.');">
                <?= csrfField() ?>
                <div class="col-12 col-lg">
                    <label class="form-label fw-semibold mb-1" for="syscohadaCompany">
                        <i class="fas fa-file-import me-1"></i> Plan comptable SYSCOHADA (OHADA)
                    </label>
                    <div class="text-muted small mb-2">
                        Ajoute en un clic les comptes standards des classes 1 à 8 à l'entreprise choisie. Les comptes existants ne sont pas modifiés.
                    </div>
                    <select class="form-select" id="syscohadaCompany" name="company_id" required>
                        <option value="">Sélectionner une entreprise</option>
                        <?php foreach ($companies as $company): ?>
                            <option value="<?= (int) $company['id'] ?>"><?= e($company['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-lg-auto">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="fas fa-download me-2"></i>Importer le plan SYSCOHADA
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Liste des comptes comptables</h5>
        </div>

        <div class="card-body">

            <?php if (!empty($accounts)): ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Numéro du compte</th>
                                <th>Intitulé</th>
                                <th>Type</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($accounts as $account): ?>

                                <tr>

                                    <td>
                                        <?= e($account['id']) ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?= e($account['account_number']) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= e($account['name']) ?>
                                    </td>

                                    <td>
                                        <?php
                                        $typeLabels = [
                                            ACCOUNT_ASSET => 'Actif',
                                            ACCOUNT_LIABILITY => 'Passif',
                                            ACCOUNT_EQUITY => 'Capitaux propres',
                                            ACCOUNT_EXPENSE => 'Charge',
                                            ACCOUNT_REVENUE => 'Produit'
                                        ];
                                        ?>

                                        <?= e($typeLabels[$account['type']] ?? $account['type']) ?>
                                    </td>

                                    <td>

                                        <?php if (!empty($account['is_active'])): ?>

                                            <span class="badge bg-success">
                                                Actif
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-secondary">
                                                Inactif
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td class="text-end">

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="text-center py-5">

                    <i class="fas fa-book fa-3x text-muted mb-3"></i>

                    <h5>Aucun compte comptable</h5>

                    <p class="text-muted">
                        Aucun compte n'a encore été enregistré.
                    </p>

                    <a
                        href="<?= url('accounting/accounts/create') ?>"
                        class="btn btn-primary"
                    >
                        <i class="fas fa-plus me-2"></i>
                        Ajouter un compte
                    </a>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php
?>