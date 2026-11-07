
<div class="auth-card auth-login-card">

    <div class="auth-heading">
        <span class="auth-kicker">Ravi de vous revoir</span>
        <h2>Connectez-vous</h2>
        <p>Accédez à votre espace de gestion comptable.</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="auth-error" role="alert">
            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
            <div>
                <?php foreach ($errors as $error): ?>
                    <p><?= e($error) ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <form action="<?= url('login') ?>" method="POST">

        <?= csrfField() ?>

        <div class="form-group">

            <label for="email" class="form-label">
                Adresse e-mail
            </label>

            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-envelope"></i>
                </span>

                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    placeholder="nom@entreprise.com"
                    value="<?= e($email ?? old('email')) ?>"
                    required
                    autocomplete="email"
                >
            </div>

        </div>

        <div class="form-group">

            <label for="password" class="form-label">
                Mot de passe
            </label>

            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-lock"></i>
                </span>

                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    placeholder="Votre mot de passe"
                    required
                    autocomplete="current-password"
                >
            </div>

        </div>

        <div class="auth-form-links">
            <a href="<?= url('forgot-password') ?>">
                Mot de passe oublié ?
            </a>
        </div>

        <button type="submit" class="auth-submit">
            <span>Accéder à mon espace</span>
            <i class="fas fa-arrow-right" aria-hidden="true"></i>
        </button>

    </form>

    <div class="auth-help">
        <span class="auth-help-icon"><i class="fas fa-headset" aria-hidden="true"></i></span>
        <span>Besoin d'aide ?
            <a href="<?= url('contact-admin') ?>">Contactez votre administrateur</a>
        </span>
    </div>

</div>
