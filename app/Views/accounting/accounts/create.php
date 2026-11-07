<?php
?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1">Nouveau compte comptable</h1>
            <p class="text-muted mb-0">
                Ajouter un compte au plan comptable.
            </p>
        </div>

        <a href="<?= url('accounting/accounts') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Retour à la liste
        </a>

    </div>

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Informations du compte</h5>
        </div>

        <div class="card-body">

            <form action="<?= url('accounting/accounts') ?>" method="POST">

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

                        <label for="account_number" class="form-label">
                            Numéro du compte <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="account_number"
                            id="account_number"
                            class="form-control"
                            placeholder="Exemple : 411000"
                            value="<?= e(old('account_number')) ?>"
                            required
                        >

                    </div>

                    <div class="col-md-6 mb-3">

                        <label for="name" class="form-label">
                            Intitulé du compte <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            id="name"
                            class="form-control"
                            placeholder="Exemple : Clients"
                            value="<?= e(old('name')) ?>"
                            required
                        >

                    </div>

                    <div class="col-md-6 mb-3">

                        <label for="type" class="form-label">
                            Type de compte <span class="text-danger">*</span>
                        </label>

                        <select
                            name="type"
                            id="type"
                            class="form-select"
                            required
                        >

                            <option value="">Sélectionner un type</option>

                            <option value="<?= ACCOUNT_ASSET ?>"
                                <?= old('type') === ACCOUNT_ASSET ? 'selected' : '' ?>>
                                Actif
                            </option>

                            <option value="<?= ACCOUNT_LIABILITY ?>"
                                <?= old('type') === ACCOUNT_LIABILITY ? 'selected' : '' ?>>
                                Passif
                            </option>

                            <option value="<?= ACCOUNT_EQUITY ?>"
                                <?= old('type') === ACCOUNT_EQUITY ? 'selected' : '' ?>>
                                Capitaux propres
                            </option>

                            <option value="<?= ACCOUNT_EXPENSE ?>"
                                <?= old('type') === ACCOUNT_EXPENSE ? 'selected' : '' ?>>
                                Charge
                            </option>

                            <option value="<?= ACCOUNT_REVENUE ?>"
                                <?= old('type') === ACCOUNT_REVENUE ? 'selected' : '' ?>>
                                Produit
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

                    <a href="<?= url('accounting/accounts') ?>" class="btn btn-light">
                        Annuler
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Enregistrer le compte
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php
?>