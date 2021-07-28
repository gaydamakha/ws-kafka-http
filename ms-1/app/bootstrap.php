<?php

declare(strict_types=1);

require 'constants.php';
require ROOT_PATH . '/vendor/autoload.php';

// Environment variables
if (file_exists(ROOT_PATH . DIRECTORY_SEPARATOR . '.env')) {
    $dotenv = Dotenv\Dotenv::createUnsafeImmutable(ROOT_PATH);
    $dotenv->load();
}

require ROOT_PATH . '/app/dependencies.php';
