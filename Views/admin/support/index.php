<div class="page-header">
    <div>
        <h1 class="page-title">Demandes de contact</h1>
        <p class="page-subtitle">Messages envoyés depuis le formulaire d’assistance.</p>
    </div>
</div>

<section class="card">
    <div class="card-header">
        <h2 class="card-title">Boîte de réception</h2>
        <span><?= count($requests ?? []) ?> demande(s)</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr><th>Date</th><th>Nom</th><th>E-mail</th><th>Objet et message</th><th>Statut</th><th>Mettre à jour</th></tr>
                </thead>
                <tbody>
                    <?php foreach (($requests ?? []) as $request): ?>
                        <?php
                        $statusLabels = ['new' => 'Nouvelle', 'in_progress' => 'En cours', 'resolved' => 'Résolue'];
                        $statusClasses = ['new' => 'bg-primary', 'in_progress' => 'bg-warning text-dark', 'resolved' => 'bg-success'];
                        $status = $request['status'] ?? 'new';
                        ?>
                        <tr>
                            <td><?= e(formatDateTime($request['created_at'] ?? null)) ?></td>
                            <td><?= e($request['name'] ?? '') ?></td>
                            <td><a href="mailto:<?= e($request['email'] ?? '') ?>"><?= e($request['email'] ?? '') ?></a></td>
                            <td>
                                <strong><?= e($request['subject'] ?? '') ?></strong>
                                <p class="mb-0 mt-1 text-muted"><?= nl2br(e($request['message'] ?? '')) ?></p>
                            </td>
                            <td><span class="badge <?= e($statusClasses[$status] ?? 'bg-secondary') ?>"><?= e($statusLabels[$status] ?? $status) ?></span></td>
                            <td>
                                <form method="POST" action="<?= url('admin/support/' . (int) $request['id'] . '/status') ?>" class="d-flex gap-2">
                                    <?= csrfField() ?>
                                    <select class="form-select form-select-sm" name="status" aria-label="Statut de la demande">
                                        <?php foreach ($statusLabels as $value => $label): ?>
                                            <option value="<?= e($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-sm btn-outline-primary" type="submit">Enregistrer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($requests)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-5">Aucune demande reçue pour le moment.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
