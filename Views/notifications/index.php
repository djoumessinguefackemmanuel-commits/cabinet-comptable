<div class="page-header">
    <div>
        <h1 class="page-title">Notifications</h1>
        <p class="page-subtitle">Consultez les informations et alertes de votre espace.</p>
    </div>
    <?php if (!empty($unreadCount)): ?>
        <form method="POST" action="<?= url('notifications/read-all') ?>">
            <?= csrfField() ?>
            <button class="btn btn-outline-primary" type="submit">Tout marquer comme lu</button>
        </form>
    <?php endif; ?>
</div>

<section class="card">
    <div class="card-header">
        <h2 class="card-title"><i class="fas fa-bell"></i> Mes notifications</h2>
        <span><?= count($notifications ?? []) ?> notification(s)</span>
    </div>
    <div class="card-body">
        <?php if (!empty($notifications)): ?>
            <div class="list-group list-group-flush">
                <?php foreach ($notifications as $notification): ?>
                    <?php
                    $status = $notification['status'] ?? NOTIFICATION_UNREAD;
                    $type = $notification['type'] ?? 'info';
                    $icons = ['success' => 'fa-check-circle text-success', 'warning' => 'fa-exclamation-triangle text-warning', 'error' => 'fa-times-circle text-danger', 'danger' => 'fa-times-circle text-danger', 'info' => 'fa-info-circle text-info'];
                    $icon = $icons[$type] ?? $icons['info'];
                    $isUnread = $status === NOTIFICATION_UNREAD;
                    ?>
                    <div class="list-group-item py-3 <?= $isUnread ? 'bg-light' : '' ?>">
                        <div class="d-flex align-items-start">
                            <div class="me-3 pt-1"><i class="fas <?= $icon ?> fa-lg"></i></div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="mb-1">
                                        <?= e($notification['title'] ?? 'Notification') ?>
                                        <?php if ($isUnread): ?><span class="badge bg-primary ms-2">Nouveau</span><?php endif; ?>
                                    </h6>
                                    <small class="text-muted"><?= !empty($notification['created_at']) ? formatDateTime($notification['created_at']) : '-' ?></small>
                                </div>
                                <p class="text-muted mb-0"><?= nl2br(e($notification['message'] ?? '')) ?></p>
                                <?php if ($isUnread): ?>
                                    <form method="POST" action="<?= url('notifications/' . (int) $notification['id'] . '/read') ?>" class="mt-2">
                                        <?= csrfField() ?>
                                        <button class="btn btn-sm btn-outline-secondary" type="submit">Marquer comme lu</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-bell-slash fa-3x"></i>
                <p>Aucune notification à consulter.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
