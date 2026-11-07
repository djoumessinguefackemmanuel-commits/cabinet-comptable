<div class="page-header">
    <div><h1 class="page-title">Modifier l'utilisateur</h1><p class="page-subtitle">Mettre à jour son profil et ses accès.</p></div>
    <a class="btn btn-outline-secondary" href="<?= url('users') ?>"><i class="fas fa-arrow-left"></i> Retour</a>
</div>
<?php if (!empty($errors)): ?><div class="alert alert-danger" role="alert"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<section class="card"><div class="card-body">
    <form method="POST" action="<?= url('users/' . (int) $user['id'] . '/update') ?>">
        <?= csrfField() ?>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label" for="first_name">Prénom</label><input class="form-control" id="first_name" name="first_name" required value="<?= e($user['first_name'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label" for="last_name">Nom</label><input class="form-control" id="last_name" name="last_name" required value="<?= e($user['last_name'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label" for="email">E-mail</label><input class="form-control" type="email" id="email" name="email" required value="<?= e($user['email'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label" for="phone">Téléphone</label><input class="form-control" id="phone" name="phone" value="<?= e($user['phone'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label" for="role_id">Rôle</label><select class="form-select" id="role_id" name="role_id" required><?php foreach (($roles ?? []) as $role): ?><option value="<?= (int) $role['id'] ?>" <?= (int) ($user['role_id'] ?? 0) === (int) $role['id'] ? 'selected' : '' ?>><?= e(ucfirst($role['name'])) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-6"><label class="form-label" for="status">Statut</label><select class="form-select" id="status" name="status"><option value="<?= USER_ACTIVE ?>" <?= ($user['status'] ?? '') === USER_ACTIVE ? 'selected' : '' ?>>Actif</option><option value="<?= USER_INACTIVE ?>" <?= ($user['status'] ?? '') === USER_INACTIVE ? 'selected' : '' ?>>Inactif</option><option value="<?= USER_SUSPENDED ?>" <?= ($user['status'] ?? '') === USER_SUSPENDED ? 'selected' : '' ?>>Suspendu</option></select></div>
        </div>
        <fieldset class="mt-4">
            <legend class="fs-6">Entreprises autorisées</legend>
            <p class="text-muted small">Ces accès limitent les données visibles par les rôles comptable et client.</p>
            <div class="row g-2">
                <?php foreach (($companies ?? []) as $company): ?>
                    <?php $companyId = (int) $company['id']; ?>
                    <div class="col-md-6">
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="company_ids[]" value="<?= $companyId ?>" <?= in_array($companyId, $assignedCompanyIds ?? [], true) ? 'checked' : '' ?>>
                            <span class="form-check-label"><?= e($company['name']) ?></span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </fieldset>
        <div class="mt-4"><button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Enregistrer</button><a class="btn btn-light" href="<?= url('users') ?>">Annuler</a></div>
    </form>
</div></section>
