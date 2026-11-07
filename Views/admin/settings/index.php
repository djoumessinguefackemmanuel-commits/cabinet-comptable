<div class="page-header">
    <div><h1 class="page-title">Paramètres</h1><p class="page-subtitle">Paramètres généraux enregistrés pour l’application.</p></div>
</div>
<section class="card"><div class="card-body">
    <form method="POST" action="<?= url('settings/update') ?>">
        <?= csrfField() ?>
        <?php if (empty($settings)): ?>
            <p class="text-muted">Aucun paramètre enregistré. Ajoutez une première valeur ci-dessous.</p>
            <div class="row g-3 mb-3">
                <div class="col-md-4"><label class="form-label" for="new_key">Clé</label><input class="form-control" id="new_key" name="setting_key" value="app_name" required></div>
                <div class="col-md-8"><label class="form-label" for="new_value">Valeur</label><input class="form-control" id="new_value" name="setting_value" value="<?= e(APP_NAME) ?>" required></div>
            </div>
        <?php else: ?>
            <?php foreach ($settings as $setting): ?>
                <div class="mb-3"><label class="form-label" for="setting-<?= e($setting['setting_key']) ?>"><?= e(ucfirst(str_replace('_', ' ', $setting['setting_key']))) ?></label><input class="form-control" id="setting-<?= e($setting['setting_key']) ?>" name="settings[<?= e($setting['setting_key']) ?>]" value="<?= e($setting['setting_value']) ?>"></div>
            <?php endforeach; ?>
        <?php endif; ?>
        <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Enregistrer</button>
    </form>
</div></section>
