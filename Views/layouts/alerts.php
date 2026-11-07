
<?php use App\Core\Session; ?>

<?php
$alertTypes = [
    'success' => [
        'icon' => 'fa-check-circle',
        'class' => 'alert-success'
    ],
    'error' => [
        'icon' => 'fa-times-circle',
        'class' => 'alert-danger'
    ],
    'warning' => [
        'icon' => 'fa-exclamation-triangle',
        'class' => 'alert-warning'
    ],
    'info' => [
        'icon' => 'fa-info-circle',
        'class' => 'alert-info'
    ]
];

foreach ($alertTypes as $type => $config):

    $message = Session::getFlash($type);

    if (!empty($message)):
?>

    <div class="alert <?= e($config['class']) ?> alert-dismissible fade show" role="alert">

        <i class="fas <?= e($config['icon']) ?>"></i>

        <span>
            <?= e($message) ?>
        </span>

        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>

    </div>

<?php
    endif;

endforeach;
?>
