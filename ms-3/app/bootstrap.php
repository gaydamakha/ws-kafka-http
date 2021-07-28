<?php

declare(strict_types=1);

use Kafka\ConsumerConfig;

require 'constants.php';
require ROOT_PATH . '/vendor/autoload.php';

// Environment variables
if (file_exists(ROOT_PATH . DIRECTORY_SEPARATOR . '.env')) {
    $dotenv = Dotenv\Dotenv::createUnsafeImmutable(ROOT_PATH);
    $dotenv->load();
}

$config = ConsumerConfig::getInstance();
$config->setMetadataRefreshIntervalMs(10000);
$config->setMetadataBrokerList($_ENV['KAFKA_BROKER_HOST']);
$config->setGroupId('test');
$config->setBrokerVersion('1.0.0');
$config->setTopics([$_ENV['KAFKA_TOPIC']]);

require ROOT_PATH . '/app/dependencies.php';
