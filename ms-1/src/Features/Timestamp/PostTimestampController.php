<?php

declare(strict_types=1);

namespace Gaydamakha\WsKafkaHttp\Ms1\Features\Timestamp;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostTimestampController
{
    private PostTimestampService $service;

    public function __construct(PostTimestampService $service)
    {
        $this->service = $service;
    }

    public function __invoke(ServerRequestInterface $httpRequest, ResponseInterface $httpResponse): ResponseInterface
    {
        $payload = json_decode($httpRequest->getBody()->__toString(), false, 512, JSON_THROW_ON_ERROR);
        $this->service->handle($payload);

        return $httpResponse->withStatus(200);
    }
}
