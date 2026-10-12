<?php

use Jgut\Slim\PHPDI\Configuration;
use Jgut\Slim\PHPDI\ContainerBuilder;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\ServerRequestCreatorFactory;
use WebSK\Config\ConfWrapper;
use WebSK\Slim\Facade;

require __DIR__ . '/../vendor/autoload.php';

$config_path = realpath(__DIR__ . '/../config/config.php');
if ($config_path === false) {
    throw new RuntimeException('Configuration file config/config.php was not found');
}
$config = require_once $config_path;
if (!is_array($config)) {
    throw new UnexpectedValueException('Application configuration must return an array');
}
$settings = $config['settings'] ?? null;
if (!is_array($settings)) {
    throw new UnexpectedValueException('Application configuration must contain a settings array');
}

$configuration = new Configuration();
$configuration->setDefinitions([$config]);
$configuration->setUseAttributes(true);

$container = ContainerBuilder::build($configuration);
$container->set(ServerRequestInterface::class, function () {
    return ServerRequestCreatorFactory::create()->createServerRequestFromGlobals();
});
ConfWrapper::setConfig($settings);

$app = new \WebSK\Logger\Demo\LoggerDemoApp($container);
Facade::setFacadeApplication($app);

$app->run();
