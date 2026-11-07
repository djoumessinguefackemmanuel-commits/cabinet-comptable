
<?php
$title = 'Détails de l’entreprise';

$company = $company ?? [];
?>

<div class="page-header">
    <div>
        <h1>Détails de l'entreprise</h1>
        <p>Consultez les informations de cette entreprise.</p>
    </div>

    <div>
        <a href="<?= url('companies') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
            Retour
        </a>

        <a
            href="<?= url('companies/' . (int) ($company['id'] ?? 0) . '/edit') ?>"
            class="btn btn-primary"
        >
            <i class="fas fa-edit"></i>
            Modifier
        </a>
    </div>
</div>

<div class="dashboard-section">

    <div class="section-header">
        <h2>Informations générales</h2>
    </div>

    <div class="row">

        <div class="col-md-6 mb-3">
            <strong>Nom de l'entreprise</strong>
            <p><?= e($company['name'] ?? '-') ?></p>
        </div>

        <div class="col-md-6 mb-3">
            <strong>Adresse email</strong>
            <p><?= e($company['email'] ?? '-') ?></p>
        </div>

        <div class="col-md-6 mb-3">
            <strong>Téléphone</strong>
            <p><?= e($company['phone'] ?? '-') ?></p>
        </div>

        <div class="col-md-6 mb-3">
            <strong>Ville</strong>
            <p><?= e($company['city'] ?? '-') ?></p>
        </div>

        <div class="col-md-12 mb-3">
            <strong>Adresse</strong>
            <p><?= e($company['address'] ?? '-') ?></p>
        </div>

        <div class="col-md-6 mb-3">
            <strong>Numéro d'identifiant fiscal</strong>
            <p><?= e($company['tax_number'] ?? '-') ?></p>
        </div>

        <div class="col-md-6 mb-3">
            <strong>Numéro d'immatriculation</strong>
            <p><?= e($company['registration_number'] ?? '-') ?></p>
        </div>

        <div class="col-md-6 mb-3">
            <strong>Date d'enregistrement</strong>
            <p>
                <?= !empty($company['created_at'])
                    ? e(formatDateTime($company['created_at']))
                    : '-' ?>
            </p>
        </div>

    </div>

</div>
