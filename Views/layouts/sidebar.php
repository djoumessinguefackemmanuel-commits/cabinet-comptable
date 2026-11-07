
<?php use App\Core\Auth; ?>

<?php
$currentUser = Auth::user();
$userRole = $currentUser['role'] ?? '';
?>

<aside class="sidebar" id="sidebar">

    <div class="sidebar-header">
        <a href="<?= url('dashboard') ?>" class="sidebar-brand">
            <i class="fas fa-calculator"></i>
            <span><?= e(APP_NAME) ?></span>
        </a>
    </div>

    <nav class="sidebar-menu">

        <div class="sidebar-section">
            <span class="sidebar-section-title">PRINCIPAL</span>

            <a href="<?= url('dashboard') ?>" class="sidebar-link">
                <i class="fas fa-tachometer-alt"></i>
                <span>Tableau de bord</span>
            </a>
        </div>

        <?php if (in_array($userRole, [ROLE_ADMIN, ROLE_EXPERT], true)): ?>

            <div class="sidebar-section">
                <span class="sidebar-section-title">ADMINISTRATION</span>

                <a href="<?= url('users') ?>" class="sidebar-link">
                    <i class="fas fa-users"></i>
                    <span>Utilisateurs</span>
                </a>

                <a href="<?= url('companies') ?>" class="sidebar-link">
                    <i class="fas fa-building"></i>
                    <span>Entreprises</span>
                </a>

                <a href="<?= url('roles') ?>" class="sidebar-link">
                    <i class="fas fa-user-shield"></i>
                    <span>Rôles et permissions</span>
                </a>
            </div>

        <?php endif; ?>

        <?php if (in_array($userRole, [ROLE_ADMIN, ROLE_EXPERT, ROLE_ACCOUNTANT], true)): ?>

            <div class="sidebar-section">
                <span class="sidebar-section-title">COMPTABILITÉ</span>

                <a href="<?= url('exercises') ?>" class="sidebar-link">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Exercices comptables</span>
                </a>

                <a href="<?= url('accounting/accounts') ?>" class="sidebar-link">
                    <i class="fas fa-list-ol"></i>
                    <span>Plan comptable</span>
                </a>

                <a href="<?= url('accounting/journals') ?>" class="sidebar-link">
                    <i class="fas fa-book"></i>
                    <span>Journaux</span>
                </a>

                <a href="<?= url('accounting/entries') ?>" class="sidebar-link">
                    <i class="fas fa-file-invoice"></i>
                    <span>Écritures comptables</span>
                </a>

                <a href="<?= url('accounting/ledger') ?>" class="sidebar-link">
                    <i class="fas fa-book-open"></i>
                    <span>Grand livre</span>
                </a>

                <a href="<?= url('accounting/balance') ?>" class="sidebar-link">
                    <i class="fas fa-balance-scale"></i>
                    <span>Balance</span>
                </a>

                <a href="<?= url('accounting/reconciliation') ?>" class="sidebar-link">
                    <i class="fas fa-exchange-alt"></i>
                    <span>Rapprochements</span>
                </a>
            </div>

            <div class="sidebar-section">
                <span class="sidebar-section-title">FISCALITÉ ET RAPPORTS</span>

                <a href="<?= url('taxes') ?>" class="sidebar-link">
                    <i class="fas fa-percent"></i>
                    <span>Fiscalité</span>
                </a>

                <a href="<?= url('reports/balance-sheet') ?>" class="sidebar-link">
                    <i class="fas fa-chart-bar"></i>
                    <span>États financiers</span>
                </a>

                <a href="<?= url('invoices') ?>" class="sidebar-link">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Factures</span>
                </a>
            </div>

        <?php endif; ?>

        <div class="sidebar-section">
            <span class="sidebar-section-title">AUTRES</span>

            <a href="<?= url('documents') ?>" class="sidebar-link">
                <i class="fas fa-folder"></i>
                <span>Documents</span>
            </a>

            <a href="<?= url('notifications') ?>" class="sidebar-link">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
            </a>

            <?php if ($userRole === ROLE_ADMIN): ?>
                <a href="<?= url('admin/support') ?>" class="sidebar-link">
                    <i class="fas fa-inbox"></i>
                    <span>Demandes de contact</span>
                </a>

                <a href="<?= url('audit') ?>" class="sidebar-link">
                    <i class="fas fa-history"></i>
                    <span>Journal d'audit</span>
                </a>

                <a href="<?= url('settings') ?>" class="sidebar-link">
                    <i class="fas fa-cog"></i>
                    <span>Paramètres</span>
                </a>
            <?php endif; ?>

        </div>

    </nav>

    <div class="sidebar-footer">
        <form action="<?= url('logout') ?>" method="POST">
            <?= csrfField() ?>
            <button type="submit" class="sidebar-link logout-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </button>
        </form>
    </div>

</aside>
