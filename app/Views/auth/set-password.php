<div class="auth-card">

    <div class="auth-card-header">
        <div class="auth-icon"><i class="fas fa-key"></i></div>
        <h2>Bienvenue<?= $firstName !== '' ? ', ' . e($firstName) : '' ?> !</h2>
        <p>Pour sécuriser votre compte, choisissez votre propre mot de passe. Vous l'utiliserez à chaque connexion.</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <?php foreach ($errors as $error): ?><div><?= e($error) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="<?= url('account/welcome') ?>" method="POST">
        <?= csrfField() ?>

        <div class="form-group mb-3">
            <label for="new_password" class="form-label">Nouveau mot de passe</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" id="new_password" name="new_password" class="form-control"
                       minlength="10" maxlength="72" required autofocus autocomplete="new-password">
            </div>
            <div class="form-text">10 caractères minimum, avec au moins une lettre et un chiffre.</div>
        </div>

        <div class="form-group mb-4">
            <label for="new_password_confirmation" class="form-label">Confirmer le mot de passe</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control"
                       minlength="10" maxlength="72" required autocomplete="new-password">
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">Enregistrer et continuer</button>
    </form>

    <form action="<?= url('logout') ?>" method="POST" class="text-center mt-3">
        <?= csrfField() ?>
        <button type="submit" class="btn btn-link btn-sm">Me déconnecter</button>
    </form>

</div>
