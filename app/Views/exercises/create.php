<div class="page-header">
    <div>
        <h1 class="page-title">Nouvel exercice comptable</h1>
        <p class="page-subtitle">Enregistrer un nouvel exercice comptable.</p>
    </div>
    <a href="<?= url('exercises') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Retour à la liste</a>
</div>

<section class="card">
    <div class="card-header"><h2 class="card-title">Informations de l'exercice</h2></div>
    <div class="card-body">
        <form action="<?= url('exercises') ?>" method="POST">
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="company_id" class="form-label">Entreprise <span class="text-danger">*</span></label>
                    <select name="company_id" id="company_id" class="form-select" required>
                        <option value="">Sélectionner une entreprise</option>
                        <?php foreach (($companies ?? []) as $company): ?>
                            <option value="<?= e($company['id']) ?>" <?= (string) old('company_id') === (string) $company['id'] ? 'selected' : '' ?>><?= e($company['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="name" class="form-label">Nom de l'exercice <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Exemple : Exercice comptable 2026" value="<?= e(old('name')) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="start_date" class="form-label">Date de début <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?= e(old('start_date')) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="end_date" class="form-label">Date de fin <span class="text-danger">*</span></label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="<?= e(old('end_date')) ?>" required>
                </div>
            </div>
            <div class="alert alert-info mt-3"><i class="fas fa-info-circle"></i> Vérifiez que la date de fin est postérieure à la date de début.</div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer l'exercice</button>
                <a href="<?= url('exercises') ?>" class="btn btn-light">Annuler</a>
            </div>
        </form>
    </div>
</section>
