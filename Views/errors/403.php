<?php
ob_start();
?>

<div class="container-fluid">

    <div class="row justify-content-center align-items-center text-center"
         style="min-height: 70vh;">

        <div class="col-md-8 col-lg-6">

            <div class="mb-4">

                <i class="fas fa-lock text-danger"
                   style="font-size: 90px;"></i>

            </div>

            <h1 class="display-1 fw-bold text-danger">
                403
            </h1>

            <h2 class="h3 mb-3">
                Accès interdit
            </h2>

            <p class="text-muted mb-4">
                Désolé, tu ne disposes pas des autorisations nécessaires
                pour accéder à cette page ou effectuer cette opération.
            </p>

            <div class="d-flex justify-content-center gap-2 flex-wrap">

                <a href="<?= url('dashboard') ?>"
                   class="btn btn-primary">

                    <i class="fas fa-home me-2"></i>
                    Retour au tableau de bord

                </a>

                <button type="button"
                        class="btn btn-outline-secondary"
                        onclick="history.back()">

                    <i class="fas fa-arrow-left me-2"></i>
                    Page précédente

                </button>

            </div>

        </div>

    </div>

</div>

<?php
$content = ob_get_clean();

require VIEW_PATH . '/layouts/main.php';
?>