
<div class="auth-card">

    <div class="auth-card-header">
        <div class="auth-icon">
            <i class="fas fa-key"></i>
        </div>

        <h2>Mot de passe oublié ?</h2>

        <p>
            Entrez votre adresse e-mail pour demander la réinitialisation
            de votre mot de passe.
        </p>
    </div>

    <form action="<?= url('forgot-password') ?>" method="POST">

        <?= csrfField() ?>

        <div class="form-group mb-4">

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
                    placeholder="Entrez votre adresse e-mail"
                    value="<?= e(old('email')) ?>"
                    required
                    autocomplete="email"
                >
            </div>

        </div>

        <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-paper-plane"></i>
            Envoyer la demande
        </button>

    </form>

    <div class="auth-form-links mt-4 text-center">
        <a href="<?= url('login') ?>">
            <i class="fas fa-arrow-left"></i>
            Retour à la connexion
        </a>
    </div>

</div>
