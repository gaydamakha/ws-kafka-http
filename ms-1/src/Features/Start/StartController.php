<?php

declare(strict_types=1);

namespace Gaydamakha\WsKafkaHttp\Ms1\Features\Start;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class StartController
{
    private StartService $service;

    public function __construct(StartService $service)
    {
        $this->service = $service;
    }

    public function __invoke(ServerRequestInterface $httpRequest, ResponseInterface $httpResponse): ResponseInterface
    {
        $this->service->start();
        return $httpResponse->withStatus(200);
    }
}
