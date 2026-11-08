
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">

    <title>
        <?= e($title ?? 'Authentification') ?> - <?= e(APP_NAME) ?>
    </title>

    <meta name="description" content="Authentification sécurisée du cabinet comptable.">

    <link rel="icon" href="<?= asset('images/favicon.ico') ?>">

    <link rel="stylesheet" href="<?= asset('css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">

    <link rel="stylesheet" href="<?= asset('css/auth.css') ?>">
</head>

<body class="auth-page">

    <main class="auth-shell">
        <section class="auth-aside" aria-label="Présentation du cabinet">
            <a class="auth-brand" href="<?= url('login') ?>">
                <span class="auth-logo">
                    <i class="fas fa-calculator" aria-hidden="true"></i>
                </span>
                <span>
                    <strong><?= e(APP_NAME) ?></strong>
                    <small>EXPERTISE &amp; CONSEIL</small>
                </span>
            </a>

            <div class="auth-aside-content">
                <span class="auth-eyebrow">
                    <i class="fas fa-shield-alt" aria-hidden="true"></i>
                    Votre espace professionnel
                </span>

                <h1>La gestion de votre cabinet, <span>en toute clarté.</span></h1>
                <p class="auth-intro">
                    Retrouvez vos dossiers, vos écritures et vos échéances
                    dans un espace unique, pensé pour votre métier.
                </p>

                <div class="auth-feature-list">
                    <div class="auth-feature">
                        <span class="auth-feature-icon"><i class="fas fa-chart-line" aria-hidden="true"></i></span>
                        <span><strong>Un pilotage simplifié</strong><small>Vos indicateurs essentiels en un coup d’œil</small></span>
                    </div>
                    <div class="auth-feature">
                        <span class="auth-feature-icon"><i class="fas fa-lock" aria-hidden="true"></i></span>
                        <span><strong>Vos données protégées</strong><small>Un accès sécurisé à vos informations</small></span>
                    </div>
                </div>
            </div>

            <div class="auth-aside-footer">
                <span class="auth-status-dot"></span>
                Un espace fiable pour accompagner votre activité
            </div>
        </section>

        <section class="auth-main">
            <div class="auth-main-content">
                <div class="auth-mobile-brand">
                    <span class="auth-logo"><i class="fas fa-calculator" aria-hidden="true"></i></span>
                    <strong><?= e(APP_NAME) ?></strong>
                </div>

                <?php require VIEW_PATH . '/layouts/alerts.php'; ?>

                <?= $content ?? '' ?>
            </div>

            <footer class="auth-footer">
                <span><i class="fas fa-lock" aria-hidden="true"></i> Connexion sécurisée</span>
            </footer>
        </section>

    </main>

    <script src="<?= asset('js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= asset('js/app.js') ?>"></script>

</body>
</html>
