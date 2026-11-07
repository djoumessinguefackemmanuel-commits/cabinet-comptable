<?php
$invoices = $invoices ?? [];
$totalInvoices = count($invoices);
$paidInvoices = 0; $pendingInvoices = 0; $draftInvoices = 0; $totalAmount = 0;
foreach ($invoices as $invoice) {
    $s = $invoice['status'] ?? '';
    if ($s === INVOICE_PAID) $paidInvoices++;
    elseif ($s === INVOICE_PENDING) $pendingInvoices++;
    elseif ($s === INVOICE_DRAFT) $draftInvoices++;
    $totalAmount += (float) ($invoice['amount'] ?? 0);
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Gestion des factures</h1>
        <p class="page-subtitle">Consultez et suivez les factures émises par le cabinet.</p>
    </div>
</div>

<div class="dashboard-stats mb-4" style="grid-template-columns: repeat(4, minmax(0,1fr));">
    <div class="stat-card"><span class="stat-icon primary"><i class="fas fa-file-invoice"></i></span><span class="stat-info"><span class="stat-label">Total factures</span><strong class="stat-value"><?= $totalInvoices ?></strong></span></div>
    <div class="stat-card"><span class="stat-icon success"><i class="fas fa-check-circle"></i></span><span class="stat-info"><span class="stat-label">Payées</span><strong class="stat-value"><?= $paidInvoices ?></strong></span></div>
    <div class="stat-card"><span class="stat-icon warning"><i class="fas fa-clock"></i></span><span class="stat-info"><span class="stat-label">En attente</span><strong class="stat-value"><?= $pendingInvoices ?></strong></span></div>
    <div class="stat-card"><span class="stat-icon info"><i class="fas fa-money-bill-wave"></i></span><span class="stat-info"><span class="stat-label">Montant total</span><strong class="stat-value" style="font-size:16px;"><?= number_format($totalAmount, 0, ',', ' ') ?> FCFA</strong></span></div>
</div>

<section class="card mb-4">
    <div class="card-header"><h2 class="card-title">Créer une facture</h2></div>
    <div class="card-body">
        <?php if (empty($companies)): ?>
            <p class="text-muted mb-0">Créez d'abord une entreprise pour pouvoir enregistrer une facture.</p>
        <?php else: ?>
            <form method="POST" action="<?= url('invoices/store') ?>">
                <?= csrfField() ?>
                <div class="row g-3">
                    <div class="col-md-3"><label class="form-label" for="invoice-company">Entreprise</label><select class="form-select" id="invoice-company" name="company_id" required><option value="">Choisir</option><?php foreach ($companies as $company): ?><option value="<?= (int) $company['id'] ?>"><?= e($company['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-2"><label class="form-label" for="invoice-exercise">Exercice</label><select class="form-select" id="invoice-exercise" name="exercise_id"><option value="">Aucun</option><?php foreach (($exercises ?? []) as $exercise): ?><option value="<?= (int) $exercise['id'] ?>"><?= e(($exercise['company_name'] ?? '') . ' - ' . $exercise['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-2"><label class="form-label" for="invoice-number">N° facture</label><input class="form-control" id="invoice-number" name="invoice_number" maxlength="50" required></div>
                    <div class="col-md-2"><label class="form-label" for="invoice-amount">Montant (FCFA)</label><input class="form-control" id="invoice-amount" name="amount" type="number" min="0" step="0.01" required></div>
                    <div class="col-md-3"><label class="form-label" for="invoice-description">Description</label><input class="form-control" id="invoice-description" name="description"></div>
                    <div class="col-md-3"><label class="form-label" for="invoice-date">Date d'émission</label><input class="form-control" id="invoice-date" name="invoice_date" type="date" value="<?= e(date('Y-m-d')) ?>" required></div>
                    <div class="col-md-3"><label class="form-label" for="invoice-due-date">Date d'échéance</label><input class="form-control" id="invoice-due-date" name="due_date" type="date"></div>
                </div>
                <button class="btn btn-primary mt-3" type="submit"><i class="fas fa-plus"></i> Enregistrer la facture</button>
            </form>
        <?php endif; ?>
    </div>
</section>

<section class="card mb-4">
    <div class="card-header"><h2 class="card-title">Filtrer</h2></div>
    <div class="card-body">
        <form method="GET" action="<?= url('invoices') ?>">
            <div class="row g-3 align-items-end">
                <div class="col-md-3"><label class="form-label" for="filter-company">Entreprise</label><select class="form-select" id="filter-company" name="company_id"><option value="">Toutes</option><?php foreach (($companies ?? []) as $company): ?><option value="<?= (int) $company['id'] ?>" <?= (int) ($filters['company_id'] ?? 0) === (int) $company['id'] ? 'selected' : '' ?>><?= e($company['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-2"><label class="form-label" for="filter-status">Statut</label><select class="form-select" id="filter-status" name="status"><option value="">Tous</option><?php foreach ([INVOICE_DRAFT => 'Brouillon', INVOICE_PENDING => 'En attente', INVOICE_PAID => 'Payée', INVOICE_CANCELLED => 'Annulée'] as $value => $label): ?><option value="<?= e($value) ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-2"><label class="form-label" for="filter-start">Du</label><input class="form-control" id="filter-start" type="date" name="start_date" value="<?= e($filters['start_date'] ?? '') ?>"></div>
                <div class="col-md-2"><label class="form-label" for="filter-end">Au</label><input class="form-control" id="filter-end" type="date" name="end_date" value="<?= e($filters['end_date'] ?? '') ?>"></div>
                <div class="col-md-3 d-flex gap-2"><button class="btn btn-outline-primary" type="submit">Filtrer</button><a class="btn btn-outline-secondary" href="<?= url('invoices') ?>">Réinitialiser</a></div>
            </div>
        </form>
    </div>
</section>

<section class="card">
    <div class="card-header"><h2 class="card-title">Liste des factures</h2><span><?= $totalInvoices ?> facture(s)</span></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>N° Facture</th><th>Entreprise</th><th>Description</th><th class="text-end">Montant</th><th>Date d'émission</th><th>Date d'échéance</th><th>Statut</th><th>Créée le</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php if (!empty($invoices)): ?>
                        <?php foreach ($invoices as $invoice): ?>
                            <?php
                            $status = $invoice['status'] ?? '';
                            $statusLabels = [INVOICE_DRAFT => 'Brouillon', INVOICE_PENDING => 'En attente', INVOICE_PAID => 'Payée', INVOICE_CANCELLED => 'Annulée'];
                            $statusClasses = [INVOICE_DRAFT => 'bg-secondary', INVOICE_PENDING => 'bg-warning text-dark', INVOICE_PAID => 'bg-success', INVOICE_CANCELLED => 'bg-danger'];
                            ?>
                            <tr>
                                <td class="fw-semibold"><?= e($invoice['invoice_number'] ?? '') ?></td>
                                <td><?= e($invoice['company_name'] ?? '-') ?></td>
                                <td><?= e($invoice['description'] ?? '-') ?></td>
                                <td class="text-end fw-semibold"><?= number_format((float) ($invoice['amount'] ?? 0), 2, ',', ' ') ?> FCFA</td>
                                <td><?= !empty($invoice['invoice_date']) ? formatDate($invoice['invoice_date']) : '-' ?></td>
                                <td><?= !empty($invoice['due_date']) ? formatDate($invoice['due_date']) : '-' ?></td>
                                <td><span class="badge <?= $statusClasses[$status] ?? 'bg-secondary' ?>"><?= e($statusLabels[$status] ?? 'Inconnu') ?></span></td>
                                <td><?= !empty($invoice['created_at']) ? formatDate($invoice['created_at']) : '-' ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <form method="POST" action="<?= url('invoices/' . (int) $invoice['id'] . '/status') ?>" class="d-flex gap-1">
                                            <?= csrfField() ?>
                                            <select class="form-select form-select-sm" name="status" aria-label="Statut de la facture">
                                                <?php foreach ([INVOICE_DRAFT => 'Brouillon', INVOICE_PENDING => 'En attente', INVOICE_PAID => 'Payée', INVOICE_CANCELLED => 'Annulée'] as $value => $label): ?>
                                                    <option value="<?= e($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button class="btn btn-sm btn-outline-primary" type="submit">OK</button>
                                        </form>
                                        <form method="POST" action="<?= url('invoices/' . (int) $invoice['id'] . '/delete') ?>" onsubmit="return confirm('Supprimer cette facture ?');">
                                            <?= csrfField() ?>
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center text-muted py-4">Aucune facture disponible.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
