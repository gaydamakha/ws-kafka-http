<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Gaydamakha\WsKafkaHttp\Ms3\Ms1Client;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Factory\AppFactory;

$builder = new ContainerBuilder();
if (in_array($_ENV['STAGING'], ['production'], true)) {
    $builder->enableCompilation(ROOT_PATH . '/temp');
    $builder->writeProxiesToFile(true, ROOT_PATH . '/temp/proxies');
}

$builder->addDefinitions([
    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);
        return AppFactory::create();
    },
    LoggerInterface::class => DI\factory(function () {
        return (new Logger('ms-3'))
            ->pushHandler(new StreamHandler('php://stdout', $_ENV['LOG_LEVEL'] ?? Logger::INFO));
    }),
    Ms1Client::class => function () {
        return new Ms1Client(
            $_ENV['MS1_HOST'],
            Psr18ClientDiscovery::find(),
            Psr17FactoryDiscovery::findUriFactory(),
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory(),
        );
    }
]);

try {
    $container = $builder->build();
} catch (Exception $e) {
    die("Could not build container");
}
