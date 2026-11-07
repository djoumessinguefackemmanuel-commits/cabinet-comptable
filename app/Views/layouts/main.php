
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= e($title ?? APP_NAME) ?> - <?= e(APP_NAME) ?>
    </title>

    <meta name="description" content="Plateforme de gestion comptable du cabinet.">

    <link rel="icon" href="<?= asset('images/favicon.ico') ?>">

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

</body>
</html>
