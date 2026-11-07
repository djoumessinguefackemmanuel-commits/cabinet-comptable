<div class="auth-card support-contact-card">
    <div class="auth-card-header">
        <div class="auth-icon"><i class="fas fa-headset" aria-hidden="true"></i></div>
        <h2>Contacter l’administrateur</h2>
        <p>Décrivez votre problème. Votre demande sera enregistrée pour que l’équipe puisse vous répondre.</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="auth-error" role="alert">
            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
            <div><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div>
        </div>
    <?php endif; ?>

    <form action="<?= url('contact-admin') ?>" method="POST">
        <?= csrfField() ?>
        <div class="form-group">
            <label for="contact-name" class="form-label">Votre nom</label>
            <input class="form-control" id="contact-name" name="name" maxlength="150" value="<?= e($old['name'] ?? '') ?>" required autocomplete="name">
        </div>
        <div class="form-group">
            <label for="contact-email" class="form-label">Votre adresse e-mail</label>
            <input class="form-control" type="email" id="contact-email" name="email" maxlength="150" value="<?= e($old['email'] ?? '') ?>" required autocomplete="email">
        </div>
        <div class="form-group">
            <label for="contact-subject" class="form-label">Objet</label>
            <input class="form-control" id="contact-subject" name="subject" maxlength="200" value="<?= e($old['subject'] ?? 'Assistance - Cabinet Comptable') ?>" required>
        </div>
        <div class="form-group">
            <label for="contact-message" class="form-label">Votre message</label>
            <textarea class="form-control" id="contact-message" name="message" rows="5" maxlength="5000" required><?= e($old['message'] ?? '') ?></textarea>
        </div>
        <button class="auth-submit" type="submit">
            <span>Envoyer la demande</span>
            <i class="fas fa-paper-plane" aria-hidden="true"></i>
        </button>
    </form>

    <div class="auth-form-links mt-4 text-center">
        <a href="<?= url('login') ?>"><i class="fas fa-arrow-left" aria-hidden="true"></i> Retour à la connexion</a>
    </div>
</div>
