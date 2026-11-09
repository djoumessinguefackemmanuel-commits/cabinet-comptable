<div class="page-header">
    <div>
        <h1 class="page-title">Sécurité du compte</h1>
        <p class="page-subtitle">Protégez votre accès avec une double authentification.</p>
    </div>
</div>

<?php if (!empty($newCodes)): ?>
    <section class="card mb-4 border-warning">
        <div class="card-header"><h2 class="card-title">Vos codes de secours</h2></div>
        <div class="card-body">
            <p>
                Conservez ces codes dans un endroit sûr. Chacun ne sert qu'<strong>une seule fois</strong>
                si vous perdez votre téléphone. <strong>Ils ne seront plus affichés.</strong>
            </p>
            <div class="row g-2 mb-3" style="max-width:420px">
                <?php foreach ($newCodes as $recoveryCode): ?>
                    <div class="col-6"><code class="d-block p-2 bg-light border rounded text-center"><?= e($recoveryCode) ?></code></div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Imprimer
            </button>
        </div>
    </section>
<?php endif; ?>

<?php if (!$configured): ?>
    <div class="alert alert-warning" role="alert">
        La double authentification n'est pas encore disponible : la variable d'environnement
        <code>APP_KEY</code> (au moins 16 caractères aléatoires) doit être configurée sur le serveur.
    </div>
<?php elseif ($enabled): ?>
    <section class="card mb-4">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-check-circle text-success me-2"></i>Double authentification activée
            </h2>
        </div>
        <div class="card-body">
            <p class="mb-1">À chaque connexion, un code de votre application d'authentification est demandé.</p>
            <p class="text-muted">Codes de secours restants : <strong><?= (int) $remaining ?></strong></p>

            <div class="row g-4">
                <div class="col-lg-6">
                    <h3 class="h6">Régénérer les codes de secours</h3>
                    <form method="POST" action="<?= url('account/security/recovery-codes') ?>">
                        <?= csrfField() ?>
                        <div class="mb-2"><input class="form-control" type="password" name="password" placeholder="Mot de passe" required autocomplete="current-password"></div>
                        <div class="mb-2"><input class="form-control" type="text" name="code" placeholder="Code à 6 chiffres" inputmode="numeric" autocomplete="one-time-code" required></div>
                        <button class="btn btn-outline-primary" type="submit">Générer de nouveaux codes</button>
                    </form>
                </div>
                <div class="col-lg-6">
                    <h3 class="h6">Désactiver la double authentification</h3>
                    <form method="POST" action="<?= url('account/security/disable') ?>">
                        <?= csrfField() ?>
                        <div class="mb-2"><input class="form-control" type="password" name="password" placeholder="Mot de passe" required autocomplete="current-password"></div>
                        <div class="mb-2"><input class="form-control" type="text" name="code" placeholder="Code à 6 chiffres" inputmode="numeric" autocomplete="one-time-code" required></div>
                        <button class="btn btn-outline-danger" type="submit">Désactiver</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
<?php else: ?>
    <section class="card mb-4">
        <div class="card-header"><h2 class="card-title">Activer la double authentification</h2></div>
        <div class="card-body">
            <ol class="mb-4">
                <li>Installez une application d'authentification (Google Authenticator, Microsoft Authenticator, Authy…).</li>
                <li>Scannez le QR code ci-dessous, ou saisissez la clé manuellement.</li>
                <li>Entrez le code à 6 chiffres affiché pour confirmer.</li>
            </ol>

            <div class="row g-4 align-items-start">
                <div class="col-md-5 text-center">
                    <div id="two-factor-qr" class="d-inline-block p-2 bg-white border rounded" style="min-width:180px;min-height:180px" aria-label="QR code de configuration"></div>
                    <p class="small text-muted mt-2 mb-1">Saisie manuelle :</p>
                    <code class="user-select-all" style="word-break:break-all"><?= e(trim(chunk_split((string) $setupSecret, 4, ' '))) ?></code>
                </div>
                <div class="col-md-7">
                    <form method="POST" action="<?= url('account/security/enable') ?>">
                        <?= csrfField() ?>
                        <label class="form-label" for="two-factor-code">Code de vérification</label>
                        <input class="form-control mb-3" id="two-factor-code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="7" placeholder="123456" required>
                        <button class="btn btn-primary" type="submit"><i class="fas fa-shield-alt me-1"></i> Activer</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script src="<?= asset('js/qrcode.js') ?>"></script>
    <script>
        (function () {
            var target = document.getElementById('two-factor-qr');
            if (!target || typeof qrcode === 'undefined') { return; }
            var qr = qrcode(0, 'M');
            qr.addData(<?= json_encode($setupUri, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?>);
            qr.make();
            target.innerHTML = qr.createSvgTag({ cellSize: 4, margin: 0, scalable: true });
            var svg = target.querySelector('svg');
            if (svg) { svg.style.width = '170px'; svg.style.height = '170px'; }
        })();
    </script>
<?php endif; ?>
