<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Slim\App;

require '../app/bootstrap.php';
/** @var ContainerInterface $container */
$app = $container->get(App::class);

require ROOT_PATH . '/app/routes.php';

$app->run();
