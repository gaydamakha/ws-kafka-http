<?php

declare(strict_types=1);

use Kafka\ProducerConfig;

require 'constants.php';
require ROOT_PATH . '/vendor/autoload.php';

// Environment variables
if (file_exists(ROOT_PATH . DIRECTORY_SEPARATOR . '.env')) {
    $dotenv = Dotenv\Dotenv::createUnsafeImmutable(ROOT_PATH);
    $dotenv->load();
}

$producerConfig = ProducerConfig::getInstance();
$producerConfig->setMetadataBrokerList($_ENV['KAFKA_BROKER_HOST']);
$producerConfig->setBrokerVersion('1.0.0');
$producerConfig->setMetadataRefreshIntervalMs(10000);
$producerConfig->setRequiredAck(1);
$producerConfig->setIsAsyn(false);
$producerConfig->setProduceInterval(500);

require ROOT_PATH . '/app/dependencies.php';
