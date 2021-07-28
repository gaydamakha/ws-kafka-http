<?php

declare(strict_types=1);

use Aura\SqlQuery\QueryFactory;
use DI\ContainerBuilder;
use Gaydamakha\WsKafkaHttp\Ms1\Features\Report\ReportService;
use Gaydamakha\WsKafkaHttp\Ms1\Features\Timestamp\AuraTimestampRepository;
use Gaydamakha\WsKafkaHttp\Ms1\Features\Timestamp\PostTimestampService;
use Gaydamakha\WsKafkaHttp\Ms1\Features\Timestamp\TimestampRepository;
use Gaydamakha\WsKafkaHttp\Ms1\Ms2Client;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use function DI\get;

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
        return (new Logger('ms-1'))
            ->pushHandler(new StreamHandler('php://stdout', $_ENV['LOG_LEVEL'] ?? Logger::INFO));
    }),
    Ms2Client::class => function () {
        return new Ms2Client($_ENV['MS2_HOST']);
    },
    PDO::class => function () {
        $dbHost = getenv('DATABASE_HOST');
        $dbPort = getenv('DATABASE_PORT');
        $dbPass = getenv('DATABASE_PASS');
        $dbUser = getenv('DATABASE_USER');
        $dbName = getenv('DATABASE_NAME');

        $pdoParams = [PDO::ATTR_PERSISTENT => false];
        if (getenv('DATABASE_USE_SSL') === 'true') {
            $pdoParams += [
                PDO::MYSQL_ATTR_SSL_CA => '/dev/null',
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
            ];
        }

        return new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8;", $dbUser, $dbPass, $pdoParams);
    },
    TimestampRepository::class => get(AuraTimestampRepository::class),
    QueryFactory::class => fn() => new QueryFactory('mysql'),
    PostTimestampService::class => function (ContainerInterface $container) {
        return new PostTimestampService(
            $container->get(TimestampRepository::class),
            $container->get(LoggerInterface::class),
            (int)$_ENV['ROUND_TRIP_DURATION'],
            $container->get(Ms2Client::class),
            $container->get(ReportService::class),
        );
    },
]);

try {
    $container = $builder->build();
} catch (Exception $e) {
    die("Could not build container");
}
