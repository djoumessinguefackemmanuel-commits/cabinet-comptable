
<?php
$title = 'Modifier une entreprise';

$company = $company ?? [];
?>

<div class="page-header">
    <div>
        <h1>Modifier une entreprise</h1>
        <p>Mettez à jour les informations de cette entreprise.</p>
    </div>

    <a href="<?= url('companies') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i>
        Retour à la liste
    </a>
</div>

<div class="dashboard-section">

    <form
        action="<?= url('companies/' . (int) ($company['id'] ?? 0) . '/update') ?>"
        method="POST"
    >

        <?= csrfField() ?>

        <div class="row">

            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">
                    Nom de l'entreprise *
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    value="<?= e(old('name', $company['name'] ?? '')) ?>"
                    required
                >
            </div>

            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">
                    Adresse email *
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    value="<?= e(old('email', $company['email'] ?? '')) ?>"
                    required
                >
            </div>

            <div class="col-md-6 mb-3">
                <label for="phone" class="form-label">
                    Téléphone *
                </label>

                <input
                    type="text"
                    id="phone"
                    name="phone"
                    class="form-control"
                    value="<?= e(old('phone', $company['phone'] ?? '')) ?>"
                    required
                >
            </div>

            <div class="col-md-6 mb-3">
                <label for="city" class="form-label">
                    Ville *
                </label>

                <input
                    type="text"
                    id="city"
                    name="city"
                    class="form-control"
                    value="<?= e(old('city', $company['city'] ?? '')) ?>"
                    required
                >
            </div>

            <div class="col-md-12 mb-3">
                <label for="address" class="form-label">
                    Adresse *
                </label>

                <textarea
                    id="address"
                    name="address"
                    class="form-control"
                    rows="3"
                    required
                ><?= e(old('address', $company['address'] ?? '')) ?></textarea>
            </div>

            <div class="col-md-6 mb-3">
                <label for="tax_number" class="form-label">
                    Numéro d'identifiant fiscal
                </label>

                <input
                    type="text"
                    id="tax_number"
                    name="tax_number"
                    class="form-control"
                    value="<?= e(old('tax_number', $company['tax_number'] ?? '')) ?>"
                >
            </div>

            <div class="col-md-6 mb-3">
                <label for="registration_number" class="form-label">
                    Numéro d'immatriculation
                </label>

                <input
                    type="text"
                    id="registration_number"
                    name="registration_number"
                    class="form-control"
                    value="<?= e(old('registration_number', $company['registration_number'] ?? '')) ?>"
                >
            </div>

        </div>

        <div class="mt-4">

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Enregistrer les modifications
            </button>

            <a href="<?= url('companies') ?>" class="btn btn-outline-secondary">
                Annuler
            </a>

        </div>

    </form>

</div>
