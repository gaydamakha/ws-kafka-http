<?php

declare(strict_types=1);

namespace Gaydamakha\WsKafkaHttp\Ms2;

use Exception;
use Gaydamakha\WsKafkaHttp\Ms2\Features\Process\Processor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use WebSocket\ConnectionException;
use WebSocket\EchoLog;
use WebSocket\Server;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/bootstrap.php';

/** @var ContainerInterface $container */

$options = array_merge([
    'port' => 8000,
    'timeout' => 200,
    'filter' => ['text', 'binary', 'ping', 'pong'],
], getopt('', ['port:', 'timeout:', 'debug']));

/** @var LoggerInterface $appLogger */
$appLogger = $container->get(LoggerInterface::class);

// If debug mode and logger is available
if (isset($options['debug'])) {
    $logger = new EchoLog();
    $options['logger'] = $logger;
    $appLogger->info('Using EchoLog for Websocket server');
}

try {
    $server = new Server($options);
} catch (ConnectionException $e) {
    $appLogger->error($e->getMessage());
    die();
}

/** @var Processor $messageProcessor */
$messageProcessor = $container->get(Processor::class);

// Force quit to close server
while (true) {
    try {
        while ($server->accept()) {
            $appLogger->info("Listening to port {$server->getPort()}");
            while (true) {
                $message = $server->receive();
                if (is_null($message)) {
                    $appLogger->info("Closing connection");
                    continue 2;
                }
                $opcode = $server->getLastOpcode();
                $appLogger->info('Got a message');
                $appLogger->debug("Got '$message' [opcode: $opcode]");
                if (in_array($opcode, ['ping', 'pong'])) {
                    $server->send($message);
                    continue;
                }
                $messageProcessor->process($message);
            }
        }
    } catch (Exception $e) {
        $appLogger->error($e->getMessage());
    }
}
