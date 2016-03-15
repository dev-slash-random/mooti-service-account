<?php
    require '../vendor/autoload.php';

    $controllers = [
        'users' => Mooti\Service\Account\Controller\User::class
    ];

    $app = new Mooti\Xizlr\Core\RestApplication($controllers);
    $app->bootstrap();
    $app->run();
