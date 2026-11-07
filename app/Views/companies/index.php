
<?php
$title = 'Gestion des entreprises';

$companies = $companies ?? [];
?>

<div class="page-header">
    <div>
        <h1>Gestion des entreprises</h1>
        <p>Consultez et gérez les entreprises enregistrées dans le cabinet.</p>
    </div>

    <a href="<?= url('companies/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        Ajouter une entreprise
    </a>
</div>

<section class="card">
    <div class="card-header"><h2 class="card-title">Liste des entreprises</h2><span><?= count($companies) ?> entreprise(s)</span></div>
    <div class="card-body"><div class="table-responsive">

        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nom de l'entreprise</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Ville</th>
                    <th>Numéro fiscal</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                <?php if (!empty($companies)): ?>

                    <?php foreach ($companies as $company): ?>

                        <tr>
                            <td><?= e($company['name'] ?? '') ?></td>
                            <td><?= e($company['email'] ?? '-') ?></td>
                            <td><?= e($company['phone'] ?? '-') ?></td>
                            <td><?= e($company['city'] ?? '-') ?></td>
                            <td><?= e($company['tax_number'] ?? '-') ?></td>

                            <td>
                                <a
                                    href="<?= url('companies/' . (int) $company['id']) ?>"
                                    class="btn btn-sm btn-outline-primary"
                                    title="Consulter"
                                >
                                    <i class="fas fa-eye"></i>
                                </a>

                                <a
                                    href="<?= url('companies/' . (int) $company['id'] . '/edit') ?>"
                                    class="btn btn-sm btn-outline-warning"
                                    title="Modifier"
                                >
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6" class="text-center">
                            Aucune entreprise enregistrée pour le moment.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>
        </table>
    </div></div>
</section>
