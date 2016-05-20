<?php
    require '../vendor/autoload.php';

    $controllers = [
        'users' => Mooti\Service\Account\Controller\User::class
    ];

    $app = new Mooti\Framework\Application\Rest\Application($controllers);
    $app->bootstrap(new Mooti\Service\Account\ServiceProvider\ServiceProvider());
    $app->run();
