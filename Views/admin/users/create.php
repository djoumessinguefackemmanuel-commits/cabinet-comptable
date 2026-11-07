<?php $formUser = $old ?? []; ?>
<div class="page-header">
    <div><h1 class="page-title">Ajouter un utilisateur</h1><p class="page-subtitle">Créer un compte et définir ses accès.</p></div>
    <a class="btn btn-outline-secondary" href="<?= url('users') ?>"><i class="fas fa-arrow-left"></i> Retour</a>
</div>
<?php if (!empty($errors)): ?><div class="alert alert-danger" role="alert"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<section class="card"><div class="card-body">
    <form method="POST" action="<?= url('users/store') ?>">
        <?= csrfField() ?>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label" for="first_name">Prénom</label><input class="form-control" id="first_name" name="first_name" required value="<?= e($formUser['first_name'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label" for="last_name">Nom</label><input class="form-control" id="last_name" name="last_name" required value="<?= e($formUser['last_name'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label" for="email">E-mail</label><input class="form-control" type="email" id="email" name="email" required value="<?= e($formUser['email'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label" for="phone">Téléphone</label><input class="form-control" id="phone" name="phone" value="<?= e($formUser['phone'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label" for="password">Mot de passe temporaire</label><input class="form-control" type="password" id="password" name="password" minlength="8" autocomplete="new-password" required></div>
            <div class="col-md-6"><label class="form-label" for="password_confirmation">Confirmer le mot de passe</label><input class="form-control" type="password" id="password_confirmation" name="password_confirmation" minlength="8" autocomplete="new-password" required></div>
            <div class="col-md-6"><label class="form-label" for="role_id">Rôle</label><select class="form-select" id="role_id" name="role_id" required><option value="">Choisir un rôle</option><?php foreach (($roles ?? []) as $role): ?><option value="<?= (int) $role['id'] ?>" <?= (string) ($formUser['role_id'] ?? '') === (string) $role['id'] ? 'selected' : '' ?>><?= e(ucfirst($role['name'])) ?></option><?php endforeach; ?></select></div>
        </div>
        <div class="mt-4"><button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Créer le compte</button><a class="btn btn-light" href="<?= url('users') ?>">Annuler</a></div>
    </form>
</div></section>
