<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Gaydamakha\WsKafkaHttp\Ms2\Features\Process\Processor;
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
        return (new Logger('ms-2'))
            ->pushHandler(new StreamHandler('php://stdout', $_ENV['LOG_LEVEL'] ?? Logger::INFO));
    }),
    Processor::class => function () {
        return new Processor($_ENV['KAFKA_TOPIC']);
    }
]);

try {
    $container = $builder->build();
} catch (Exception $e) {
    die("Could not build container");
}
