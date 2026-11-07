
<div class="auth-card">

    <div class="auth-card-header">

        <div class="auth-icon">
            <i class="fas fa-lock"></i>
        </div>

        <h2>Nouveau mot de passe</h2>

        <p>
            Définissez un nouveau mot de passe pour accéder à votre compte.
        </p>

    </div>

    <form action="<?= url('reset-password') ?>" method="POST">

        <?= csrfField() ?>

        <input
            type="hidden"
            name="token"
            value="<?= e($token ?? '') ?>"
        >

        <div class="form-group mb-3">

            <label for="password" class="form-label">
                Nouveau mot de passe
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
                    placeholder="Entrez votre nouveau mot de passe"
                    minlength="8"
                    required
                    autocomplete="new-password"
                >
            </div>

            <small class="form-text text-muted">
                Le mot de passe doit contenir au moins 8 caractères.
            </small>

        </div>

        <div class="form-group mb-4">

            <label for="password_confirmation" class="form-label">
                Confirmer le nouveau mot de passe
            </label>

            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-lock"></i>
                </span>

                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="form-control"
                    placeholder="Confirmez votre nouveau mot de passe"
                    minlength="8"
                    required
                    autocomplete="new-password"
                >
            </div>

        </div>

        <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-save"></i>
            Réinitialiser le mot de passe
        </button>

    </form>

    <div class="auth-form-links mt-4 text-center">
        <a href="<?= url('login') ?>">
            <i class="fas fa-arrow-left"></i>
            Retour à la connexion
        </a>
    </div>

</div>
