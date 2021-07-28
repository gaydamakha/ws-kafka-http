<?php

declare(strict_types=1);

namespace Gaydamakha\WsKafkaHttp\Ms1\Features\Report;

use DateTime;
use Gaydamakha\WsKafkaHttp\Ms1\Features\Timestamp\PostTimestampService;
use Gaydamakha\WsKafkaHttp\Ms1\Features\Timestamp\TimestampRepository;
use Psr\Log\LoggerInterface;
use stdClass;

class ReportService
{
    private LoggerInterface $logger;
    private TimestampRepository $timestampRepository;

    public function __construct(LoggerInterface $logger, TimestampRepository $timestampRepository)
    {
        $this->logger = $logger;
        $this->timestampRepository = $timestampRepository;
    }

    public function report(stdClass $timestamp): void
    {
        $sessionId = $timestamp->session_id;
        $this->logger->info("Session #$sessionId");
        $startTimestamp = new DateTime($timestamp->MC1_timestamp);
        $endTimestamp = new DateTime($timestamp->end_timestamp);
        $this->logger->info("Time of interacting: " . PostTimestampService::mdiff($endTimestamp, $startTimestamp));
        $numberOfRoundTrips = $this->timestampRepository->count($sessionId);
        $this->logger->info("Number of round trips: $numberOfRoundTrips");
    }
}
