<div class="page-header">
    <div><h1 class="page-title">Rapprochement bancaire</h1><p class="page-subtitle">Comparez les mouvements bancaires aux écritures du cabinet.</p></div>
</div>
<section class="card"><div class="card-body">
    <div class="empty-state">
        <i class="fas fa-exchange-alt" aria-hidden="true"></i>
        <h2>Source bancaire requise</h2>
        <p>Le rapprochement n’est pas encore opérationnel : l’application ne contient pas de table ni de procédure d’import des relevés bancaires.</p>
        <p>Les journaux disponibles : <?= count($journals ?? []) ?> · Les exercices disponibles : <?= count($exercises ?? []) ?></p>
    </div>
</div></section>
