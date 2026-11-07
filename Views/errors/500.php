<?php
ob_start();
?>

<div class="container-fluid">

    <div class="row justify-content-center align-items-center text-center"
         style="min-height: 70vh;">

        <div class="col-md-8 col-lg-6">

            <div class="mb-4">

                <i class="fas fa-server text-danger"
                   style="font-size: 90px;"></i>

            </div>

            <h1 class="display-1 fw-bold text-danger">
                500
            </h1>

            <h2 class="h3 mb-3">
                Erreur interne du serveur
            </h2>

            <p class="text-muted mb-4">
                Une erreur inattendue est survenue lors du traitement
                de ta demande. Nous t'invitons à réessayer dans quelques
                instants. Si le problème persiste, contacte l'administrateur
                du cabinet.
            </p>

            <div class="d-flex justify-content-center gap-2 flex-wrap">

                <a href="<?= url('dashboard') ?>"
                   class="btn btn-primary">

                    <i class="fas fa-home me-2"></i>
                    Retour au tableau de bord

                </a>

                <button type="button"
                        class="btn btn-outline-secondary"
                        onclick="window.location.reload()">

                    <i class="fas fa-redo me-2"></i>
                    Réessayer

                </button>

            </div>

        </div>

    </div>

</div>

<?php
$content = ob_get_clean();

require VIEW_PATH . '/layouts/main.php';
?>