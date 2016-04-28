<?php
    require '../vendor/autoload.php';

    $controllers = [
        'users' => Mooti\Service\Account\Controller\User::class
    ];

    $app = new Mooti\Framework\Rest\Application($controllers);
    $app->bootstrap();
    $app->run();
