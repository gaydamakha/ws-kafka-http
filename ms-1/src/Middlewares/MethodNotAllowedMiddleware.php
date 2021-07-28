<?php

declare(strict_types=1);

namespace Gaydamakha\WsKafkaHttp\Ms1\Middlewares;

use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpMethodNotAllowedException;

class MethodNotAllowedMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (HttpMethodNotAllowedException $e) {
            return Psr17FactoryDiscovery::findResponseFactory()
                ->createResponse(405)
                ->withBody(Psr17FactoryDiscovery::findStreamFactory()->createStream(json_encode(["error" => $e->getMessage()])));

        }
    }
}
