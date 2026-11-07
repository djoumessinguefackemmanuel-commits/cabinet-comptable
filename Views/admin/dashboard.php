<?php

$stats = $stats ?? [];
$totalUsers = (int) ($stats['total_users'] ?? 0);
$activeUsers = (int) ($stats['active_users'] ?? 0);
$totalCompanies = (int) ($stats['total_companies'] ?? 0);
$totalExercises = (int) ($stats['total_exercises'] ?? 0);
$totalEntries = (int) ($stats['total_entries'] ?? 0);
$recentUsers = $recentUsers ?? [];
$recentCompanies = $recentCompanies ?? [];
$recentActivities = $recentActivities ?? [];

?>

<div class="admin-dashboard">
    <header class="dashboard-welcome">
        <div>
            <span class="dashboard-eyebrow">Espace d'administration</span>
            <h1>Tableau de bord</h1>
            <p>Vue d’ensemble de l’activité de votre cabinet.</p>
        </div>
        <div class="dashboard-admin-illustration" role="img" aria-label="Illustration d’une administratrice gérant le cabinet">
            <svg viewBox="0 0 160 120" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <rect x="14" y="12" width="103" height="73" rx="9" fill="#fff" fill-opacity=".12" stroke="#fff" stroke-opacity=".42"/>
                <rect x="25" y="24" width="49" height="6" rx="3" fill="#b9e8dd"/>
                <rect x="25" y="38" width="35" height="5" rx="2.5" fill="#fff" fill-opacity=".62"/>
                <rect x="25" y="52" width="12" height="20" rx="3" fill="#8ed1c2"/>
                <rect x="43" y="46" width="12" height="26" rx="3" fill="#fff" fill-opacity=".8"/>
                <rect x="61" y="57" width="12" height="15" rx="3" fill="#f3c879"/>
                <circle cx="112" cy="68" r="25" fill="#f3c879"/>
                <path d="M75 112c3-19 15-29 34-29s31 10 34 29" fill="#d9f0e9"/>
                <path d="M99 62c0-11 7-18 17-18s17 7 17 18v6c0 11-7 19-17 19s-17-8-17-19z" fill="#d99570"/>
                <path d="M97 61c0-14 8-22 20-22 10 0 18 7 18 18-5-3-9-8-11-12-5 7-14 11-27 12z" fill="#263e58"/>
                <path d="M82 103l17-10 15 13 14-13 15 10v9H82z" fill="#28577f"/>
                <path d="M141 24l10 4v9c0 9-5 15-10 18-6-3-10-9-10-18v-9z" fill="#8ed1c2"/>
                <path d="M137 36l3 3 6-7" fill="none" stroke="#173b63" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/>
            </svg>
        </div>
        <a class="dashboard-welcome-action" href="<?= url('users') ?>">
            <i class="fas fa-users" aria-hidden="true"></i>
            Gérer les utilisateurs
        </a>
    </header>

    <section class="dashboard-stats" aria-label="Indicateurs clés">
        <article class="stat-card">
            <span class="stat-icon primary"><i class="fas fa-users" aria-hidden="true"></i></span>
            <span class="stat-info">
                <span class="stat-label">Utilisateurs</span>
                <strong class="stat-value"><?= $totalUsers ?></strong>
                <span class="stat-description"><?= $activeUsers ?> compte(s) actif(s)</span>
            </span>
        </article>
        <article class="stat-card">
            <span class="stat-icon success"><i class="fas fa-building" aria-hidden="true"></i></span>
            <span class="stat-info">
                <span class="stat-label">Entreprises</span>
                <strong class="stat-value"><?= $totalCompanies ?></strong>
                <span class="stat-description">Dossiers enregistrés</span>
            </span>
        </article>
        <article class="stat-card">
            <span class="stat-icon warning"><i class="fas fa-calendar-alt" aria-hidden="true"></i></span>
            <span class="stat-info">
                <span class="stat-label">Exercices comptables</span>
                <strong class="stat-value"><?= $totalExercises ?></strong>
                <span class="stat-description">Exercices enregistrés</span>
            </span>
        </article>
        <article class="stat-card">
            <span class="stat-icon info"><i class="fas fa-book" aria-hidden="true"></i></span>
            <span class="stat-info">
                <span class="stat-label">Écritures comptables</span>
                <strong class="stat-value"><?= $totalEntries ?></strong>
                <span class="stat-description">Écritures enregistrées</span>
            </span>
        </article>
    </section>

    <section class="dashboard-grid equal-columns">
        <article class="card dashboard-section">
            <div class="card-header">
                <div class="dashboard-section-heading">
                    <span class="dashboard-section-icon primary"><i class="fas fa-users" aria-hidden="true"></i></span>
                    <div>
                        <h2 class="card-title">Utilisateurs récents</h2>
                        <p>Les derniers comptes ajoutés</p>
                    </div>
                </div>
                <a class="dashboard-card-link" href="<?= url('users') ?>">Tout voir <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            </div>
            <div class="card-body dashboard-table-wrap">
                <?php if ($recentUsers === []): ?>
                    <div class="empty-state"><i class="fas fa-user-slash" aria-hidden="true"></i><p>Aucun utilisateur récent.</p></div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table dashboard-table">
                            <thead><tr><th>Nom</th><th>Adresse e-mail</th><th>Rôle</th><th>Statut</th></tr></thead>
                            <tbody>
                                <?php foreach ($recentUsers as $user): ?>
                                    <?php $status = $user['status'] ?? ''; ?>
                                    <tr>
                                        <td class="dashboard-name"><?= e(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) ?></td>
                                        <td><?= e($user['email'] ?? '') ?></td>
                                        <td><?= e(ucfirst($user['role_name'] ?? 'Utilisateur')) ?></td>
                                        <td><span class="dashboard-status <?= $status === USER_ACTIVE ? 'is-active' : 'is-inactive' ?>"><?= $status === USER_ACTIVE ? 'Actif' : 'Inactif' ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </article>

        <article class="card dashboard-section">
            <div class="card-header">
                <div class="dashboard-section-heading">
                    <span class="dashboard-section-icon success"><i class="fas fa-building" aria-hidden="true"></i></span>
                    <div>
                        <h2 class="card-title">Entreprises récentes</h2>
                        <p>Les derniers dossiers enregistrés</p>
                    </div>
                </div>
                <a class="dashboard-card-link" href="<?= url('companies') ?>">Tout voir <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            </div>
            <div class="card-body dashboard-table-wrap">
                <?php if ($recentCompanies === []): ?>
                    <div class="empty-state"><i class="fas fa-folder-open" aria-hidden="true"></i><p>Aucune entreprise enregistrée.</p></div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table dashboard-table">
                            <thead><tr><th>Entreprise</th><th>Adresse e-mail</th><th>Téléphone</th><th>Ville</th></tr></thead>
                            <tbody>
                                <?php foreach ($recentCompanies as $company): ?>
                                    <tr>
                                        <td class="dashboard-name"><?= e($company['name'] ?? '') ?></td>
                                        <td><?= e($company['email'] ?? '—') ?></td>
                                        <td><?= e($company['phone'] ?? '—') ?></td>
                                        <td><?= e($company['city'] ?? '—') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </article>
    </section>

    <section class="card dashboard-section dashboard-activity">
        <div class="card-header">
            <div class="dashboard-section-heading">
                <span class="dashboard-section-icon info"><i class="fas fa-history" aria-hidden="true"></i></span>
                <div>
                    <h2 class="card-title">Activité récente</h2>
                    <p>Dernières opérations consignées dans le journal d’audit</p>
                </div>
            </div>
            <a class="dashboard-card-link" href="<?= url('audit') ?>">Journal d’audit <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="card-body">
            <?php if ($recentActivities === []): ?>
                <div class="empty-state"><i class="fas fa-clipboard-list" aria-hidden="true"></i><p>Aucune activité enregistrée pour le moment.</p></div>
            <?php else: ?>
                <div class="activity-list">
                    <?php foreach ($recentActivities as $activity): ?>
                        <div class="activity-item">
                            <span class="activity-icon"><i class="fas fa-bolt" aria-hidden="true"></i></span>
                            <div class="activity-content">
                                <strong class="activity-title"><?= e($activity['action'] ?? 'Activité') ?></strong>
                                <p class="activity-description"><?= e($activity['description'] ?? '') ?></p>
                            </div>
                            <time class="activity-time"><?= e(formatDateTime($activity['created_at'] ?? null)) ?></time>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
