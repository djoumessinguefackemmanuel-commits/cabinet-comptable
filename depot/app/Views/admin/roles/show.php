<div class="page-header">
    <div><h1 class="page-title">Rôle : <?= e(ucfirst($role['name'] ?? '')) ?></h1><p class="page-subtitle">Détails du profil d’accès.</p></div>
    <a class="btn btn-outline-secondary" href="<?= url('roles') ?>"><i class="fas fa-arrow-left"></i> Retour aux rôles</a>
</div>
<section class="card"><div class="card-body">
    <dl class="row mb-0"><dt class="col-sm-3">Nom</dt><dd class="col-sm-9"><?= e(ucfirst($role['name'] ?? '')) ?></dd><dt class="col-sm-3">Description</dt><dd class="col-sm-9"><?= e($role['description'] ?? 'Aucune description') ?></dd></dl>
</div></section>
