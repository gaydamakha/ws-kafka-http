<?php

declare(strict_types=1);

use Gaydamakha\WsKafkaHttp\Ms1\Features\Start\StartController;
use Gaydamakha\WsKafkaHttp\Ms1\Features\Stop\StopController;
use Gaydamakha\WsKafkaHttp\Ms1\Features\Timestamp\PostTimestampController;
use Gaydamakha\WsKafkaHttp\Ms1\Middlewares\MethodNotAllowedMiddleware;
use Gaydamakha\WsKafkaHttp\Ms1\Middlewares\NotFoundMiddleware;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/** @var Slim\App $app */
/** @var ContainerInterface $container */

$app->get('/start/', StartController::class);
$app->get('/stop/', StopController::class);
$app->post('/timestamp', PostTimestampController::class);
$app->addErrorMiddleware(true, true, true, $container->get(LoggerInterface::class));

$app->addMiddleware(new MethodNotAllowedMiddleware());
$app->addMiddleware(new NotFoundMiddleware());
