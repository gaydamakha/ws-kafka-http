<?php

declare(strict_types=1);

namespace Gaydamakha\WsKafkaHttp\Ms3;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use stdClass;

class Ms1Client
{
    private string $ms1Uri;
    private ClientInterface $httpClient;
    private UriFactoryInterface $uriFactory;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;

    public function __construct(
        string $ms1Uri,
        ClientInterface $httpClient,
        UriFactoryInterface $uriFactory,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->ms1Uri = $ms1Uri;
        $this->httpClient = $httpClient;
        $this->uriFactory = $uriFactory;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }

    public function sendMessage(stdClass $message): void
    {
        $uri = $this->uriFactory->createUri($this->ms1Uri)->withPath('/timestamp');
        $request = $this->requestFactory->createRequest('POST', $uri)
            ->withHeader('Content-type', 'application/json')
            ->withBody($this->streamFactory->createStream(json_encode($message)));

        $this->httpClient->sendRequest($request);
    }
}
