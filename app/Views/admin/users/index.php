<div class="page-header">
    <div>
        <h1 class="page-title">Utilisateurs</h1>
        <p class="page-subtitle">Comptes et accès à la plateforme.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('users/create') ?>"><i class="fas fa-user-plus"></i> Ajouter un utilisateur</a>
</div>

<section class="card">
    <div class="card-header"><h2 class="card-title">Comptes enregistrés</h2><span><?= count($users ?? []) ?> utilisateur(s)</span></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Nom</th><th>E-mail</th><th>Rôle</th><th>Statut</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach (($users ?? []) as $user): ?>
                        <tr>
                            <td><?= e(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) ?></td>
                            <td><?= e($user['email'] ?? '') ?></td>
                            <td><?= e(ucfirst($user['role_name'] ?? '')) ?></td>
                            <td>
                                <?= e(match ($user['status'] ?? '') {
                                    USER_ACTIVE => 'Actif',
                                    USER_INACTIVE => 'Désactivé',
                                    USER_SUSPENDED => 'Bloqué',
                                    default => ucfirst($user['status'] ?? '')
                                }) ?>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= url('users/' . (int) $user['id'] . '/edit') ?>">
                                        <i class="fas fa-edit me-1"></i> Modifier
                                    </a>
                                    <?php if ((int) $user['id'] !== \App\Core\Auth::id()): ?>
                                        <form class="d-flex align-items-center gap-1" method="POST" action="<?= url('users/' . (int) $user['id'] . '/status') ?>">
                                            <?= csrfField() ?>
                                            <label class="visually-hidden" for="user-status-<?= (int) $user['id'] ?>">Statut de <?= e($user['email'] ?? '') ?></label>
                                            <select class="form-select form-select-sm" id="user-status-<?= (int) $user['id'] ?>" name="status" aria-label="Statut du compte">
                                                <option value="<?= USER_ACTIVE ?>" <?= ($user['status'] ?? '') === USER_ACTIVE ? 'selected' : '' ?>>Actif</option>
                                                <option value="<?= USER_INACTIVE ?>" <?= ($user['status'] ?? '') === USER_INACTIVE ? 'selected' : '' ?>>Désactivé</option>
                                                <option value="<?= USER_SUSPENDED ?>" <?= ($user['status'] ?? '') === USER_SUSPENDED ? 'selected' : '' ?>>Bloqué</option>
                                            </select>
                                            <button class="btn btn-sm btn-outline-secondary" type="submit">Appliquer</button>
                                        </form>
                                        <form method="POST" action="<?= url('users/' . (int) $user['id'] . '/two-factor/reset') ?>" data-confirm-delete data-confirm-message="Réinitialiser la double authentification de cet utilisateur ? Il pourra se connecter avec son seul mot de passe et devra la réactiver.">
                                            <?= csrfField() ?>
                                            <button class="btn btn-sm btn-outline-secondary" type="submit" title="Téléphone perdu : supprimer la double authentification">
                                                <i class="fas fa-mobile-alt me-1"></i> Réinit. 2FA
                                            </button>
                                        </form>
                                        <form method="POST" action="<?= url('users/' . (int) $user['id'] . '/delete') ?>" data-confirm-delete data-confirm-message="Supprimer définitivement cet utilisateur ? Cette action peut être impossible s’il possède des documents ou des données historiques.">
                                            <?= csrfField() ?>
                                            <button class="btn btn-sm btn-outline-danger" type="submit">
                                                <i class="fas fa-trash me-1"></i> Supprimer
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small">Votre compte administrateur ne peut pas être bloqué ni supprimé ici.</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?><tr><td colspan="5" class="text-center text-muted">Aucun utilisateur enregistré.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
