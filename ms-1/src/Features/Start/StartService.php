<?php

declare(strict_types=1);

namespace Gaydamakha\WsKafkaHttp\Ms1\Features\Start;

use DateTime;
use Gaydamakha\WsKafkaHttp\Ms1\Features\Timestamp\TimestampRepository;
use Gaydamakha\WsKafkaHttp\Ms1\Ms2Client;
use Psr\Log\LoggerInterface;

class StartService
{
    private const START_CODE = 1;
    private Ms2Client $ms2Client;
    private TimestampRepository $timestampRepository;
    private LoggerInterface $logger;

    public function __construct(Ms2Client $ms2Client, TimestampRepository $messagesRepository, LoggerInterface $logger)
    {
        $this->ms2Client = $ms2Client;
        $this->timestampRepository = $messagesRepository;
        $this->logger = $logger;
    }

    public function start(): void
    {
        $sessionId = ($this->timestampRepository->getLastSessionId() ?? 0) + 1;
        $this->logger->info("Starting interaction for session #$sessionId");
        $message = [
            'id' => self::START_CODE,
            'session_id' => $sessionId,
            'MC1_timestamp' => (new DateTime())->format(DATE_FORMAT),
            'MC2_timestamp' => null,
            'MC3_timestamp' => null,
        ];
        $this->ms2Client->send(json_encode($message));
        $this->ms2Client->close();
    }
}
