<?php
?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1">Nouveau journal comptable</h1>
            <p class="text-muted mb-0">
                Ajouter un journal au système comptable.
            </p>
        </div>

        <a href="<?= url('accounting/journals') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Retour à la liste
        </a>

    </div>

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Informations du journal</h5>
        </div>

        <div class="card-body">

            <form action="<?= url('accounting/journals') ?>" method="POST">

                <?= csrfField() ?>

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label for="company_id" class="form-label">Entreprise <span class="text-danger">*</span></label>
                        <select name="company_id" id="company_id" class="form-select" required>
                            <option value="">Sélectionner une entreprise</option>
                            <?php foreach (($companies ?? []) as $company): ?>
                                <option value="<?= (int) $company['id'] ?>"><?= e($company['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">

                        <label for="code" class="form-label">
                            Code du journal <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="code"
                            id="code"
                            class="form-control"
                            placeholder="Exemple : AC"
                            value="<?= e(old('code')) ?>"
                            required
                        >

                    </div>

                    <div class="col-md-6 mb-3">

                        <label for="name" class="form-label">
                            Nom du journal <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            id="name"
                            class="form-control"
                            placeholder="Exemple : Journal des achats"
                            value="<?= e(old('name')) ?>"
                            required
                        >

                    </div>

                    <div class="col-md-6 mb-3">

                        <label for="type" class="form-label">
                            Type de journal <span class="text-danger">*</span>
                        </label>

                        <select
                            name="type"
                            id="type"
                            class="form-select"
                            required
                        >

                            <option value="">Sélectionner un type</option>

                            <option value="purchases"
                                <?= old('type') === 'purchases' ? 'selected' : '' ?>>
                                Achats
                            </option>

                            <option value="sales"
                                <?= old('type') === 'sales' ? 'selected' : '' ?>>
                                Ventes
                            </option>

                            <option value="cash"
                                <?= old('type') === 'cash' ? 'selected' : '' ?>>
                                Trésorerie
                            </option>

                            <option value="bank"
                                <?= old('type') === 'bank' ? 'selected' : '' ?>>
                                Banque
                            </option>

                            <option value="miscellaneous"
                                <?= old('type') === 'miscellaneous' ? 'selected' : '' ?>>
                                Opérations diverses
                            </option>

                        </select>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label for="is_active" class="form-label">
                            Statut <span class="text-danger">*</span>
                        </label>

                        <select
                            name="is_active"
                            id="is_active"
                            class="form-select"
                            required
                        >

                            <option value="1"
                                <?= old('is_active', '1') === '1' ? 'selected' : '' ?>>
                                Actif
                            </option>

                            <option value="0"
                                <?= old('is_active') === '0' ? 'selected' : '' ?>>
                                Inactif
                            </option>

                        </select>

                    </div>

                </div>

                <div class="d-flex justify-content-end gap-2">

                    <a href="<?= url('accounting/journals') ?>" class="btn btn-light">
                        Annuler
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Enregistrer le journal
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php
?>