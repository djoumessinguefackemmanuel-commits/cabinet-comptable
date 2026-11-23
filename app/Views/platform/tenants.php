<div class="page-header">
    <div>
        <h1 class="page-title">Cabinets clients</h1>
        <p class="page-subtitle">Chaque cabinet a ses propres entreprises, utilisateurs, journaux et paramètres : il ne voit jamais ceux des autres.</p>
    </div>
</div>

<section class="card mb-4">
    <div class="card-body">
        <h2 class="h5 mb-3">Nouveau cabinet</h2>
        <form method="POST" action="<?= url('platform/tenants/store') ?>" class="row g-3">
            <?= csrfField() ?>
            <div class="col-12 col-md-6">
                <label class="form-label" for="name">Nom du cabinet *</label>
                <input class="form-control" id="name" name="name" required maxlength="150">
            </div>
            <div class="col-12 col-md-6"></div>
            <div class="col-12 col-md-4">
                <label class="form-label" for="first_name">Prénom de l'administrateur *</label>
                <input class="form-control" id="first_name" name="first_name" required>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label" for="last_name">Nom de l'administrateur *</label>
                <input class="form-control" id="last_name" name="last_name" required>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label" for="email">E-mail de l'administrateur *</label>
                <input class="form-control" id="email" name="email" type="email" required>
            </div>
            <div class="col-12">
                <button class="btn btn-primary" type="submit"><i class="fas fa-plus me-2"></i>Créer le cabinet et son administrateur</button>
                <span class="text-muted small ms-2">Un mot de passe temporaire est généré ; l'administrateur le change à sa première connexion.</span>
            </div>
        </form>
    </div>
</section>

<section class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr><th>Cabinet</th><th>Entreprises</th><th>Utilisateurs</th><th>Statut</th><th>Créé le</th><th class="text-end">Action</th></tr>
                </thead>
                <tbody>
                <?php foreach ($tenants as $tenant): ?>
                    <tr>
                        <td><strong><?= e($tenant['name']) ?></strong></td>
                        <td><?= (int) $tenant['companies_count'] ?></td>
                        <td><?= (int) $tenant['users_count'] ?></td>
                        <td>
                            <?php if ($tenant['status'] === 'active'): ?>
                                <span class="badge bg-success">Actif</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Suspendu</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e(date('d/m/Y', strtotime((string) $tenant['created_at']))) ?></td>
                        <td class="text-end">
                            <form method="POST" action="<?= url('platform/tenants/' . (int) $tenant['id'] . '/toggle') ?>"
                                  onsubmit="return confirm('<?= $tenant['status'] === 'active' ? 'Suspendre ce cabinet ? Ses utilisateurs ne pourront plus se connecter.' : 'Réactiver ce cabinet ?' ?>');">
                                <?= csrfField() ?>
                                <button class="btn btn-sm <?= $tenant['status'] === 'active' ? 'btn-outline-danger' : 'btn-outline-success' ?>" type="submit">
                                    <?= $tenant['status'] === 'active' ? 'Suspendre' : 'Réactiver' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
