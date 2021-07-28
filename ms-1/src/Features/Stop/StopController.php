<?php

declare(strict_types=1);

namespace Gaydamakha\WsKafkaHttp\Ms1\Features\Stop;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class StopController
{
    private StopService $service;

    public function __construct(StopService $service)
    {
        $this->service = $service;
    }

    public function __invoke(ServerRequestInterface $httpRequest, ResponseInterface $httpResponse): ResponseInterface
    {
        $this->service->stop();
        return $httpResponse->withStatus(200);
    }
}
