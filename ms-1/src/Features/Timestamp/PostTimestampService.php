<?php

declare(strict_types=1);

namespace Gaydamakha\WsKafkaHttp\Ms1\Features\Timestamp;

use DateTime;
use DateTimeInterface;
use Gaydamakha\WsKafkaHttp\Ms1\Features\Report\ReportService;
use Gaydamakha\WsKafkaHttp\Ms1\Ms2Client;
use Psr\Log\LoggerInterface;
use stdClass;

class PostTimestampService
{
    private TimestampRepository $repository;
    private LoggerInterface $logger;
    private int $roundTripDuration; // in seconds
    private Ms2Client $ms2Client;
    private ReportService $reportService;

    public function __construct(
        TimestampRepository $repository,
        LoggerInterface $logger,
        int $roundTripDuration,
        Ms2Client $ms2Client,
        ReportService $reportService
    ) {
        $this->repository = $repository;
        $this->logger = $logger;
        $this->roundTripDuration = $roundTripDuration;
        $this->ms2Client = $ms2Client;
        $this->reportService = $reportService;
    }

    public function handle(stdClass $message): void
    {
        $this->logger->info('Got a post timestamp request');
        $sessionId = $message->session_id;
        $finished = $this->repository->isFinished($sessionId);
        if (!$finished) {
            $endTimestamp = new DateTime();
            $message->end_timestamp = $endTimestamp->format(DATE_FORMAT);
            $this->repository->store($message->session_id, $message);
            $this->logger->debug('Received: ' . json_encode($message));
            $ms1Timestamp = new DateTime($message->MC1_timestamp);
            //If duration of interval has not been exceeded
            if ((int)self::mdiff($endTimestamp, $ms1Timestamp) < $this->roundTripDuration) {
                $this->logger->info("Starting again for session #$sessionId");
                $this->ms2Client->send(json_encode($message));
                $this->ms2Client->close();
            } else {
                $this->repository->finish($sessionId);
                $this->reportService->report($message);
            }
        }
    }

    public static function mdiff(DateTimeInterface $date1, DateTimeInterface $date2): string
    {
        return number_format(abs((float)$date1->format('U.u') - (float)$date2->format('U.u')), 6);
    }
}
