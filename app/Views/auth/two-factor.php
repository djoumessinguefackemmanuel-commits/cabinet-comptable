<div class="auth-card">

    <div class="auth-card-header">
        <div class="auth-icon">
            <i class="fas fa-shield-alt"></i>
        </div>

        <h2>Vérification en deux étapes</h2>

        <p>
            Entrez le code à 6 chiffres affiché par votre application
            d'authentification, ou l'un de vos codes de secours.
        </p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <?php foreach ($errors as $error): ?>
                <div><?= e($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="<?= url('login/2fa') ?>" method="POST">

        <?= csrfField() ?>

        <div class="form-group mb-4">

            <label for="code" class="form-label">Code de vérification</label>

            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-key"></i></span>
                <input
                    type="text"
                    id="code"
                    name="code"
                    class="form-control"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    maxlength="12"
                    placeholder="123456"
                    required
                    autofocus
                >
            </div>

        </div>

        <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-check"></i>
            Vérifier et me connecter
        </button>

    </form>

    <div class="auth-card-footer text-center mt-3">
        <a href="<?= url('login') ?>">Retour à la connexion</a>
    </div>

</div>
