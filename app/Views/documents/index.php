<?php use App\Core\Auth; ?>
<div class="page-header">
    <div><h1 class="page-title">Documents</h1><p class="page-subtitle">Déposez et retrouvez les pièces justificatives du cabinet.</p></div>
</div>
<section class="card mb-4">
    <div class="card-header"><h2 class="card-title">Téléverser un document</h2></div>
    <div class="card-body">
        <form method="POST" action="<?= url('documents/upload') ?>" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label" for="company_id">Entreprise</label><select class="form-select" name="company_id" id="company_id" required><option value="">Choisir une entreprise</option><?php foreach (($companies ?? []) as $company): ?><option value="<?= (int) $company['id'] ?>" <?= (int) ($selectedCompany ?? 0) === (int) $company['id'] ? 'selected' : '' ?>><?= e($company['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label" for="exercise_id">Exercice (facultatif)</label><select class="form-select" name="exercise_id" id="exercise_id"><option value="">Aucun exercice</option><?php foreach (($exercises ?? []) as $exercise): ?><option value="<?= (int) $exercise['id'] ?>"><?= e(($exercise['company_name'] ?? '') . ' - ' . $exercise['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label" for="document">Fichier (PDF, image ou document, 5 Mo max.)</label><input class="form-control" type="file" name="document" id="document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" required></div>
            </div>
            <button class="btn btn-primary mt-3" type="submit"><i class="fas fa-upload"></i> Envoyer le document</button>
        </form>
    </div>
</section>
<section class="card">
    <div class="card-header"><h2 class="card-title">Documents disponibles</h2><span><?= count($documents ?? []) ?> document(s)</span></div>
    <div class="card-body"><div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th>Nom</th><th>Entreprise</th><th>Déposé par</th><th>Format</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach (($documents ?? []) as $document): ?>
                <tr>
                    <td><?= e($document['name'] ?? '') ?></td>
                    <td><?= e($document['company_name'] ?? '—') ?></td>
                    <td><?= e($document['user_name'] ?? '—') ?></td>
                    <td><?= e(strtoupper($document['file_type'] ?? '')) ?></td>
                    <td><?= e(formatDateTime($document['created_at'] ?? null)) ?></td>
                    <td class="d-flex gap-2">
                        <a class="btn btn-sm btn-outline-primary" href="<?= url('documents/' . (int) $document['id'] . '/download') ?>">Télécharger</a>
                        <?php if ((Auth::user()['role'] ?? '') === ROLE_ADMIN || (int) ($document['user_id'] ?? 0) === (int) Auth::id()): ?>
                            <form method="POST" action="<?= url('documents/' . (int) $document['id'] . '/delete') ?>" onsubmit="return confirm('Supprimer ce document ?');">
                                <?= csrfField() ?>
                                <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($documents)): ?><tr><td colspan="6" class="text-center text-muted">Aucun document enregistré.</td></tr><?php endif; ?>
        </tbody>
    </table></div></div>
</section>
