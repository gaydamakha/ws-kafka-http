<?php

declare(strict_types=1);

namespace Gaydamakha\WsKafkaHttp\Ms2\Features\Process;

use DateTime;
use Kafka\Producer;

class Processor
{
    private string $kafkaTopic;

    public function __construct(string $kafkaTopic)
    {
        $this->kafkaTopic = $kafkaTopic;
    }

    public function process(string $message): void
    {
        $message = json_decode($message, false, 512, JSON_THROW_ON_ERROR);
        $message->MC2_timestamp = (new DateTime())->format(DATE_FORMAT);
        $producer = new Producer();
        $producer->send([
            [
                'topic' => $this->kafkaTopic,
                'value' => json_encode($message),
                'key' => ''
            ]
        ]);
    }
}
