<?php

declare(strict_types=1);

namespace Gaydamakha\WsKafkaHttp\Ms3;

use DateTime;
use Kafka\Consumer;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/bootstrap.php';

/** @var ContainerInterface $container */
/** @var \Gaydamakha\WsKafkaHttp\Ms3\Ms1Client $ms1Client */
$ms1Client = $container->get(Ms1Client::class);
/** @var LoggerInterface $logger */
$logger = $container->get(LoggerInterface::class);

$consumer = new Consumer();
$consumer->start(function ($topic, $part, $message) use ($ms1Client, $logger) {
    $payload = json_decode($message['message']['value'], false, 512, JSON_THROW_ON_ERROR);
    $logger->info('Got a message');
    $logger->debug('Received: ' . json_encode($payload));
    $payload->MC3_timestamp = (new DateTime())->format(DATE_FORMAT);
    $ms1Client->sendMessage($payload);
});
