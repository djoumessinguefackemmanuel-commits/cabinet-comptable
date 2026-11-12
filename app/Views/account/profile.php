<?php
$fullName = trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? ''));
$initials = mb_strtoupper(mb_substr((string) ($profile['first_name'] ?? ''), 0, 1) . mb_substr((string) ($profile['last_name'] ?? ''), 0, 1));
$roleLabels = ['admin' => 'Administrateur', 'expert' => 'Expert-comptable', 'accountant' => 'Comptable', 'client' => 'Client'];
$roleLabel = $roleLabels[$profile['role'] ?? ''] ?? ucfirst((string) ($profile['role'] ?? 'Utilisateur'));
$dateFr = static function (?string $value): string {
    if ($value === null || $value === '') {
        return '—';
    }
    $time = strtotime($value);
    return $time ? date('d/m/Y à H:i', $time) : '—';
};
$val = static fn (string $key) => $old[$key] ?? ($profile[$key] ?? '');
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Mon profil</h1>
        <p class="page-subtitle">Vos informations personnelles et la sécurité de votre compte.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <section class="card">
            <div class="card-body text-center">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle"
                     style="width:96px;height:96px;background:var(--primary,#173b63);color:#fff;font-size:2rem;font-weight:700"
                     aria-hidden="true"><?= e($initials !== '' ? $initials : '?') ?></div>
                <h2 class="h5 mb-1"><?= e($fullName) ?></h2>
                <span class="badge bg-primary mb-3"><?= e($roleLabel) ?></span>

                <dl class="text-start small mb-0">
                    <dt class="text-muted">E-mail (identifiant de connexion)</dt>
                    <dd class="text-break"><?= e($profile['email'] ?? '') ?></dd>

                    <dt class="text-muted">Statut du compte</dt>
                    <dd><?= ($profile['status'] ?? '') === 'active' ? 'Actif' : e((string) ($profile['status'] ?? '')) ?></dd>

                    <dt class="text-muted">Membre depuis</dt>
                    <dd><?= e($dateFr($profile['created_at'] ?? null)) ?></dd>

                    <dt class="text-muted">Dernière connexion</dt>
                    <dd><?= e($dateFr($profile['last_login_at'] ?? null)) ?></dd>

                    <dt class="text-muted">Double authentification</dt>
                    <dd>
                        <?php if ($twoFactor): ?>
                            <i class="fas fa-check-circle text-success"></i> Activée
                        <?php else: ?>
                            <i class="fas fa-exclamation-circle text-warning"></i> Non activée
                        <?php endif; ?>
                        — <a href="<?= url('account/security') ?>">Gérer</a>
                    </dd>

                    <?php if (!empty($companies)): ?>
                        <dt class="text-muted">Entreprise(s) rattachée(s)</dt>
                        <dd class="mb-0"><?= e(implode(', ', $companies)) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </section>
    </div>

    <div class="col-lg-8">
        <section class="card mb-4">
            <div class="card-header"><h2 class="card-title">Informations personnelles</h2></div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0"><?php foreach ($errors as $message): ?><li><?= e($message) ?></li><?php endforeach; ?></ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= url('account/profile') ?>" novalidate>
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="first_name">Prénom</label>
                            <input class="form-control" id="first_name" name="first_name" maxlength="100" required
                                   autocomplete="given-name" value="<?= e((string) $val('first_name')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="last_name">Nom</label>
                            <input class="form-control" id="last_name" name="last_name" maxlength="100" required
                                   autocomplete="family-name" value="<?= e((string) $val('last_name')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="phone">Téléphone</label>
                            <input class="form-control" id="phone" name="phone" maxlength="30" type="tel"
                                   autocomplete="tel" placeholder="+237 6 00 00 00 00" value="<?= e((string) $val('phone')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email_ro">E-mail</label>
                            <input class="form-control" id="email_ro" value="<?= e($profile['email'] ?? '') ?>" disabled>
                            <div class="form-text">Pour changer d'adresse e-mail, contactez votre administrateur.</div>
                        </div>
                    </div>
                    <button class="btn btn-primary mt-3" type="submit"><i class="fas fa-save me-1"></i> Enregistrer</button>
                </form>
            </div>
        </section>

        <section class="card">
            <div class="card-header"><h2 class="card-title">Changer le mot de passe</h2></div>
            <div class="card-body">
                <?php if (!empty($passwordErrors)): ?>
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0"><?php foreach ($passwordErrors as $message): ?><li><?= e($message) ?></li><?php endforeach; ?></ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= url('account/profile/password') ?>" novalidate>
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="current_password">Mot de passe actuel</label>
                            <input class="form-control" type="password" id="current_password" name="current_password"
                                   required autocomplete="current-password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="new_password">Nouveau mot de passe</label>
                            <input class="form-control" type="password" id="new_password" name="new_password"
                                   minlength="10" maxlength="72" required autocomplete="new-password">
                            <div class="form-text">10 caractères minimum, avec au moins une lettre et un chiffre.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="new_password_confirmation">Confirmer</label>
                            <input class="form-control" type="password" id="new_password_confirmation"
                                   name="new_password_confirmation" minlength="10" maxlength="72" required autocomplete="new-password">
                        </div>
                    </div>
                    <button class="btn btn-outline-primary mt-3" type="submit"><i class="fas fa-key me-1"></i> Modifier le mot de passe</button>
                </form>
            </div>
        </section>
    </div>
</div>
