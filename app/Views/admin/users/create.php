<?php $formUser = $old ?? []; ?>
<div class="page-header">
    <div><h1 class="page-title">Ajouter un utilisateur</h1><p class="page-subtitle">Créer un compte et définir ses accès.</p></div>
    <a class="btn btn-outline-secondary" href="<?= url('users') ?>"><i class="fas fa-arrow-left"></i> Retour</a>
</div>
<?php if (!empty($errors)): ?><div class="alert alert-danger" role="alert"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<section class="card"><div class="card-body">
    <form method="POST" action="<?= url('users/store') ?>">
        <?= csrfField() ?>
<?php if (\App\Core\Tenant::isOwner()): ?>

    <div class="mb-3">
        <label for="tenant_id" class="form-label">Cabinet *</label>
        <select name="tenant_id" id="tenant_id" class="form-select" required>
            <option value="">Sélectionner un cabinet</option>
            <?php foreach (\App\Core\Tenant::all() as $tenantRow): ?>
                <option value="<?= (int) $tenantRow['id'] ?>" <?= (int) ($old['tenant_id'] ?? 0) === (int) $tenantRow['id'] ? 'selected' : '' ?>><?= e($tenantRow['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
<?php endif; ?>

        <div class="row g-3">
            <div class="col-md-6"><label class="form-label" for="first_name">Prénom</label><input class="form-control" id="first_name" name="first_name" required value="<?= e($formUser['first_name'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label" for="last_name">Nom</label><input class="form-control" id="last_name" name="last_name" required value="<?= e($formUser['last_name'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label" for="email">E-mail</label><input class="form-control" type="email" id="email" name="email" required value="<?= e($formUser['email'] ?? '') ?>"></div>
            <div class="col-md-6"><?php $phoneValue = $formUser['phone'] ?? ''; $phoneRequired = false; include __DIR__ . '/../../partials/phone_field.php'; ?></div>
            <div class="col-md-6">
                <label class="form-label" for="password">Mot de passe temporaire</label>
                <div class="input-group">
                    <input class="form-control" type="password" id="password" name="password" minlength="10" autocomplete="new-password" required>
                    <button class="btn btn-outline-secondary" type="button" id="generatePassword" title="Générer un mot de passe sûr et facile à saisir"><i class="fas fa-magic me-1"></i> Générer</button>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Afficher le mot de passe"><i class="fas fa-eye"></i></button>
                </div>
                <div class="form-text">10 caractères minimum. Vous le transmettrez à la personne à l'étape suivante.</div>
            </div>
            <div class="col-md-6"><label class="form-label" for="password_confirmation">Confirmer le mot de passe</label><input class="form-control" type="password" id="password_confirmation" name="password_confirmation" minlength="10" autocomplete="new-password" required></div>
            <div class="col-md-6"><label class="form-label" for="role_id">Rôle</label><select class="form-select" id="role_id" name="role_id" required><option value="">Choisir un rôle</option><?php foreach (($roles ?? []) as $role): ?><option value="<?= (int) $role['id'] ?>" <?= (string) ($formUser['role_id'] ?? '') === (string) $role['id'] ? 'selected' : '' ?>><?= e(ucfirst($role['name'])) ?></option><?php endforeach; ?></select></div>
            <div class="col-12">
                <input type="hidden" name="force_change" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="force_change" name="force_change" value="1" checked>
                    <label class="form-check-label" for="force_change">Exiger que la personne choisisse son propre mot de passe à sa première connexion (recommandé)</label>
                </div>
            </div>
        </div>
        <div class="mt-4"><button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Créer le compte</button><a class="btn btn-light" href="<?= url('users') ?>">Annuler</a></div>
    </form>
</div></section>

<script>
(function () {
    var pwd = document.getElementById('password'), conf = document.getElementById('password_confirmation');
    var alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
    function generate() {
        var raw;
        do {
            var bytes = new Uint32Array(12); crypto.getRandomValues(bytes);
            raw = Array.prototype.map.call(bytes, function (b) { return alphabet[b % alphabet.length]; }).join('');
        } while (!/[A-Za-z]/.test(raw) || !/\d/.test(raw));
        return raw.match(/.{4}/g).join('-');
    }
    document.getElementById('generatePassword').addEventListener('click', function () {
        pwd.value = conf.value = generate(); pwd.type = 'text'; conf.type = 'text';
    });
    document.getElementById('togglePassword').addEventListener('click', function () {
        var show = pwd.type === 'password'; pwd.type = show ? 'text' : 'password'; conf.type = pwd.type;
    });
})();
</script>
