
<?php use App\Core\Auth; ?>

<?php
$currentUser = Auth::user();
?>

<header class="navbar-top">

    <div class="navbar-left">

        <button
            type="button"
            class="sidebar-toggle mobile-menu-toggle"
            id="sidebarToggle"
            aria-label="Ouvrir le menu"
            aria-expanded="false"
            aria-controls="sidebar"
        >
            <i class="fas fa-bars"></i>
        </button>

        <div class="navbar-page-title">
            <span><?= e(APP_NAME) ?></span>
        </div>

    </div>

    <div class="navbar-right">

        <a href="<?= url('notifications') ?>" class="navbar-icon notification-link" title="Notifications">
            <i class="fas fa-bell"></i>
        </a>

        <div class="navbar-user">

            <div class="navbar-user-avatar">
                <i class="fas fa-user"></i>
            </div>

            <div class="navbar-user-info">
                <span class="navbar-user-name">
                    <?= e(trim(
                        ($currentUser['first_name'] ?? '') . ' ' .
                        ($currentUser['last_name'] ?? '')
                    )) ?>
                </span>

                <small class="navbar-user-role">
                    <?= e(ucfirst($currentUser['role'] ?? 'Utilisateur')) ?>
                </small>
            </div>

        </div>

        <form action="<?= url('logout') ?>" method="POST">
            <?= csrfField() ?>
            <button type="submit" class="navbar-logout" title="Déconnexion">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </form>

    </div>

</header>
