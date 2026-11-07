<div class="page-header">
    <div><h1 class="page-title">Journal d'audit</h1><p class="page-subtitle">Historique des actions enregistrées dans l’application.</p></div>
</div>
<section class="card">
    <div class="card-header"><h2 class="card-title">Activités récentes</h2><span><?= count($logs ?? []) ?> entrée(s)</span></div>
    <div class="card-body"><div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th>Date</th><th>Utilisateur</th><th>Action</th><th>Élément</th><th>Description</th></tr></thead>
        <tbody>
            <?php foreach (($logs ?? []) as $log): ?>
                <tr><td><?= e(formatDateTime($log['created_at'] ?? null)) ?></td><td><?= e(trim(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? '')) ?: 'Système') ?></td><td><?= e($log['action'] ?? '') ?></td><td><?= e(($log['entity'] ?? '') . (!empty($log['entity_id']) ? ' #' . $log['entity_id'] : '')) ?></td><td><?= e($log['description'] ?? '—') ?></td></tr>
            <?php endforeach; ?>
            <?php if (empty($logs)): ?><tr><td colspan="5" class="text-center text-muted">Aucune activité enregistrée.</td></tr><?php endif; ?>
        </tbody>
    </table></div></div>
</section>
