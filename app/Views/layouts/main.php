
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">

    <title>
        <?= e($title ?? APP_NAME) ?> - <?= e(APP_NAME) ?>
    </title>

    <meta name="description" content="Plateforme de gestion comptable du cabinet.">

    <link rel="icon" href="<?= asset('images/favicon.ico') ?>">
    <link rel="icon" type="image/svg+xml" href="/assets/favicon.svg">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/assets/icons/apple-touch-icon.png">
    <meta name="theme-color" content="#173b63">

    <link rel="stylesheet" href="<?= asset('css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/responsive.css') ?>">

    <?php if (!empty($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link rel="stylesheet" href="<?= asset($css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body>

    <?php require VIEW_PATH . '/layouts/navbar.php'; ?>

    <div class="app-container">

        <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>
        <div class="sidebar-overlay" aria-hidden="true"></div>

        <main class="main-content">

            <?php require VIEW_PATH . '/layouts/alerts.php'; ?>

            <div class="page-content">
                <?= $content ?? '' ?>
            </div>

            <?php require VIEW_PATH . '/layouts/footer.php'; ?>

        </main>

    </div>

    <script src="<?= asset('js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= asset('js/app.js') ?>"></script>

    <?php if (!empty($extraJs)): ?>
        <?php foreach ($extraJs as $js): ?>
            <script src="<?= asset($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <script src="/assets/js/install.js" defer></script>
</body>
</html>
