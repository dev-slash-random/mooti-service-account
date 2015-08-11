<?php
require '../vendor/autoload.php';

$moduleName       = 'account';
$environmentName  = 'dev';
$defaultConfigDir = __DIR__.'/../config';
$userConfigDir    = '/etc/mooti/config';

$config      = new \Mooti\Xizlr\Core\Config($moduleName, $environmentName, $defaultConfigDir, $userConfigDir);
$application = new \Mooti\Xizlr\Core\Application;

$response = $application->run($config, $_SERVER, $_REQUEST);

$headers = $response->getHeaders();

foreach ($headers as $header) {
    header($header);
}

echo $response->getContent();
