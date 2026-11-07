<div class="page-header">
    <div><h1 class="page-title">Rôles et permissions</h1><p class="page-subtitle">Profils d’accès configurés pour le cabinet.</p></div>
</div>
<section class="card">
    <div class="card-header"><h2 class="card-title">Rôles disponibles</h2><span><?= count($roles ?? []) ?> rôle(s)</span></div>
    <div class="card-body">
        <div class="table-responsive"><table class="table table-hover align-middle">
            <thead><tr><th>Rôle</th><th>Description</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach (($roles ?? []) as $role): ?>
                    <tr><td><strong><?= e(ucfirst($role['name'] ?? '')) ?></strong></td><td><?= e($role['description'] ?? '—') ?></td><td><a class="btn btn-sm btn-outline-primary" href="<?= url('roles/' . (int) $role['id']) ?>">Détails</a></td></tr>
                <?php endforeach; ?>
                <?php if (empty($roles)): ?><tr><td colspan="3" class="text-center text-muted">Aucun rôle configuré.</td></tr><?php endif; ?>
            </tbody>
        </table></div>
    </div>
</section>
