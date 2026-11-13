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
                <div id="avatarZone" class="border border-2 border-dashed rounded-3 p-3 mb-3" style="border-style:dashed!important">
                    <div id="avatarPreview" class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle overflow-hidden"
                         style="width:112px;height:112px;background:var(--primary,#173b63);color:#fff;font-size:2.2rem;font-weight:700">
                        <?php if (!empty($avatarVersion)): ?>
                            <img src="<?= url('account/avatar/' . (int) $profile['id']) ?>?v=<?= (int) $avatarVersion ?>"
                                 alt="Photo de profil" style="width:100%;height:100%;object-fit:cover;display:block">
                        <?php else: ?>
                            <span aria-hidden="true"><?= e($initials !== '' ? $initials : '?') ?></span>
                        <?php endif; ?>
                    </div>

                    <form id="avatarForm" method="POST" action="<?= url('account/profile/avatar') ?>" enctype="multipart/form-data">
                        <?= csrfField() ?>
                        <input type="file" id="avatarInput" name="avatar" accept="image/jpeg,image/png,image/webp" class="d-none">
                        <label for="avatarInput" class="btn btn-outline-primary btn-sm mb-1">
                            <i class="fas fa-camera me-1"></i> Choisir une photo
                        </label>
                        <button type="submit" id="avatarSave" class="btn btn-primary btn-sm mb-1" disabled>
                            <i class="fas fa-upload me-1"></i> Enregistrer la photo
                        </button>
                        <div class="form-text">JPEG, PNG ou WebP. Vous pouvez aussi glisser l'image ici.</div>
                        <div id="avatarMessage" class="small mt-2 text-muted" role="status"></div>
                    </form>

                    <?php if (!empty($avatarVersion)): ?>
                        <form method="POST" action="<?= url('account/profile/avatar/delete') ?>" class="mt-2"
                              onsubmit="return confirm('Supprimer votre photo de profil ?');">
                            <?= csrfField() ?>
                            <button type="submit" class="btn btn-link btn-sm text-danger p-0">
                                <i class="fas fa-trash-alt me-1"></i> Supprimer la photo
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

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

        <section class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <h2 class="card-title mb-0">Activité récente</h2>
                <?php if (!empty($activity)): ?>
                    <form method="POST" action="<?= url('account/profile/activity/clear') ?>"
                          onsubmit="return confirm('Vider tout votre historique d\'activité ?');">
                        <?= csrfField() ?>
                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-broom me-1"></i> Tout effacer</button>
                    </form>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($activity)): ?>
                    <p class="text-muted mb-0">Aucune activité à afficher.</p>
                <?php else: ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($activity as $row): ?>
                            <li class="d-flex justify-content-between align-items-center gap-3 py-2 border-bottom">
                                <span class="flex-grow-1"><?= e($row['description'] ?: $row['action']) ?></span>
                                <small class="text-muted text-nowrap"><?= e($dateFr($row['created_at'] ?? null)) ?></small>
                                <form method="POST" action="<?= url('account/profile/activity/' . (int) $row['id'] . '/delete') ?>" class="m-0">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Supprimer cette ligne" aria-label="Supprimer cette ligne">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <p class="small text-muted mt-3 mb-0">
                    Cet historique est personnel. Le journal d'audit du cabinet conserve les traces des actions
                    importantes et reste consultable par l'administrateur.
                </p>
            </div>
        </section>
    </div>
</div>

<script src="<?= asset('js/profile.js') ?>"></script>
