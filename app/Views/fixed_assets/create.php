<div class="page-header"><div><h1 class="page-title">Nouvelle immobilisation</h1></div>
<a class="btn btn-outline-secondary" href="<?= url('fixed-assets') ?>">Retour</a></div>
<section class="card"><div class="card-body">
<form method="post" action="<?= url('fixed-assets/store') ?>" class="row g-3">
    <?= csrfField() ?>
    <div class="col-md-6"><label class="form-label" for="company_id">Entreprise</label>
        <select class="form-control" name="company_id" id="company_id" required>
            <?php foreach ($companies as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
        </select></div>
    <div class="col-md-6"><label class="form-label" for="category">Catégorie</label>
        <select class="form-control" name="category" id="category" required>
            <?php foreach ($categories as $k => $c): ?><option value="<?= e($k) ?>" data-years="<?= (int) $c['years'] ?>"><?= e($c['label']) ?> (compte <?= e($c['asset']) ?>)</option><?php endforeach; ?>
        </select></div>
    <div class="col-12"><label class="form-label" for="name">Désignation</label><input class="form-control" id="name" name="name" maxlength="200" required placeholder="Ex. : Ordinateur portable Dell"></div>
    <div class="col-md-3"><label class="form-label" for="acquisition_date">Date d’acquisition</label><input class="form-control" type="date" id="acquisition_date" name="acquisition_date" required></div>
    <div class="col-md-3"><label class="form-label" for="cost">Coût d’acquisition</label><input class="form-control" id="cost" name="cost" inputmode="decimal" required></div>
    <div class="col-md-3"><label class="form-label" for="residual_value">Valeur résiduelle</label><input class="form-control" id="residual_value" name="residual_value" inputmode="decimal" value="0"></div>
    <div class="col-md-3"><label class="form-label" for="useful_life_years">Durée (années)</label><input class="form-control" type="number" min="1" max="50" id="useful_life_years" name="useful_life_years" value="<?= (int) (array_values($categories)[0]['years'] ?? 5) ?>" required></div>
    <div class="col-12"><button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Enregistrer</button></div>
</form>
<script>
document.getElementById('category').addEventListener('change', function () {
  var y = this.options[this.selectedIndex].getAttribute('data-years');
  if (y) { document.getElementById('useful_life_years').value = y; }
});
</script>
</div></section>
