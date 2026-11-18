<?php
$loginUrl = url('login');
$name = trim(($invited['first_name'] ?? '') . ' ' . ($invited['last_name'] ?? ''));
$message = "Bonjour " . ($invited['first_name'] ?? '') . ",\n\n"
    . "Votre espace " . APP_NAME . " est prêt.\n\n"
    . "Lien de connexion : " . $loginUrl . "\n"
    . "Identifiant (e-mail) : " . ($invited['email'] ?? '') . "\n"
    . "Mot de passe temporaire : " . $plainPassword . "\n"
    . ($mustChange ? "\nÀ votre première connexion, vous choisirez votre propre mot de passe.\n" : "")
    . "\nPensez à supprimer ce message après votre première connexion.";
?>
<div class="page-header">
    <div>
        <h1 class="page-title"><?= $isReset ? 'Nouveau mot de passe généré' : 'Compte créé' ?></h1>
        <p class="page-subtitle">Envoyez ces informations à <?= e($name) ?>. Elles ne seront plus affichées ensuite.</p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= url('users') ?>"><i class="fas fa-arrow-left"></i> Retour aux utilisateurs</a>
</div>

<section class="card">
    <div class="card-body">
        <div class="alert alert-warning" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i>
            Copiez ces informations maintenant : le mot de passe n'est jamais conservé en clair et ne pourra pas être réaffiché.
            Si besoin, utilisez « Nouveau mot de passe » dans la liste des utilisateurs.
        </div>

        <dl class="row mb-4">
            <dt class="col-sm-3">Lien de connexion</dt>
            <dd class="col-sm-9"><a href="<?= e($loginUrl) ?>" class="text-break"><?= e($loginUrl) ?></a></dd>
            <dt class="col-sm-3">Identifiant</dt>
            <dd class="col-sm-9 text-break"><?= e($invited['email'] ?? '') ?></dd>
            <dt class="col-sm-3">Mot de passe temporaire</dt>
            <dd class="col-sm-9"><code class="fs-5 user-select-all" id="tempPassword"><?= e($plainPassword) ?></code></dd>
        </dl>

        <?php if ($mustChange): ?>
            <p class="text-muted"><i class="fas fa-info-circle me-1"></i> À sa première connexion, la personne devra choisir son propre mot de passe.</p>
        <?php endif; ?>

        <label class="form-label" for="inviteMessage">Message prêt à envoyer</label>
        <textarea id="inviteMessage" class="form-control mb-3" rows="9" readonly><?= e($message) ?></textarea>

        <div class="d-flex flex-wrap gap-2">
            <button type="button" id="copyMessage" class="btn btn-primary"><i class="fas fa-copy me-1"></i> Copier le message</button>
            <a class="btn btn-success" target="_blank" rel="noopener"
               href="https://wa.me/?text=<?= rawurlencode($message) ?>"><i class="fab fa-whatsapp me-1"></i> Envoyer par WhatsApp</a>
            <a class="btn btn-outline-primary"
               href="mailto:<?= e($invited['email'] ?? '') ?>?subject=<?= rawurlencode('Votre accès ' . APP_NAME) ?>&body=<?= rawurlencode($message) ?>"><i class="fas fa-envelope me-1"></i> Envoyer par e-mail</a>
        </div>
    </div>
</section>

<script>
(function () {
    var button = document.getElementById('copyMessage');
    var area = document.getElementById('inviteMessage');
    if (!button || !area) { return; }
    button.addEventListener('click', function () {
        var done = function () { button.innerHTML = '<i class="fas fa-check me-1"></i> Message copié'; };
        if (navigator.clipboard && navigator.clipboard.writeText) { navigator.clipboard.writeText(area.value).then(done); return; }
        area.select(); document.execCommand('copy'); done();
    });
})();
</script>
