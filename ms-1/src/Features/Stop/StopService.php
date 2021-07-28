<?php

declare(strict_types=1);

namespace Gaydamakha\WsKafkaHttp\Ms1\Features\Stop;

use Gaydamakha\WsKafkaHttp\Ms1\Features\Report\ReportService;
use Gaydamakha\WsKafkaHttp\Ms1\Features\Timestamp\TimestampRepository;
use Psr\Log\LoggerInterface;

class StopService
{
    private TimestampRepository $timestampRepository;
    private ReportService $reportService;
    private LoggerInterface $logger;

    public function __construct(
        TimestampRepository $timestampRepository,
        ReportService $reportService,
        LoggerInterface $logger
    ) {
        $this->timestampRepository = $timestampRepository;
        $this->reportService = $reportService;
        $this->logger = $logger;
    }

    public function stop(): void
    {
        $this->logger->info('Got a request for stopping interactions');
        $distinctSessionIds = [];
        //Not finished timestamps are ordered by end_timestamp DESC
        // so take only the first one, finish and report it
        foreach ($this->timestampRepository->getNotFinished() as $timestamp) {
            $sessionId = $timestamp->session_id;
            if (!in_array($sessionId, $distinctSessionIds)) {
                $distinctSessionIds[] = $sessionId;
                $this->timestampRepository->finish($sessionId);
                $this->reportService->report($timestamp);
            }
        }
    }
}
