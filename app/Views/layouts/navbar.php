
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

        <div class="dropdown export-menu" id="exportMenu" hidden>
            <button type="button" class="navbar-icon" data-bs-toggle="dropdown" aria-expanded="false" title="Exporter cette page" aria-label="Exporter cette page">
                <i class="fas fa-download"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#" data-export="pdf"><i class="fas fa-file-pdf me-2 text-danger"></i>PDF</a></li>
                <li><a class="dropdown-item" href="#" data-export="xlsx"><i class="fas fa-file-excel me-2 text-success"></i>Excel (.xlsx)</a></li>
                <li><a class="dropdown-item" href="#" data-export="csv"><i class="fas fa-file-csv me-2 text-secondary"></i>CSV</a></li>
            </ul>
        </div>

        <a href="<?= url('notifications') ?>" class="navbar-icon notification-link" title="Notifications">
            <i class="fas fa-bell"></i>
        </a>

        <a href="<?= url('account/security') ?>" class="navbar-icon" title="Sécurité du compte">
            <i class="fas fa-shield-alt"></i>
        </a>

        <a class="navbar-user" href="<?= url('account/profile') ?>" title="Mon profil" style="text-decoration:none;color:inherit">

            <div class="navbar-user-avatar" style="overflow:hidden">
                <?php if (!empty($currentUser['avatar_v'])): ?>
                    <img src="<?= url('account/avatar/' . (int) ($currentUser['id'] ?? 0)) ?>?v=<?= (int) $currentUser['avatar_v'] ?>"
                         alt="" style="width:100%;height:100%;object-fit:cover;display:block">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
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

        </a>

        <form action="<?= url('logout') ?>" method="POST">
            <?= csrfField() ?>
            <button type="submit" class="navbar-logout" title="Déconnexion">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </form>

    </div>

</header>
