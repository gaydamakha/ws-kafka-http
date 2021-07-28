<?php

declare(strict_types=1);

namespace Gaydamakha\WsKafkaHttp\Ms1\Features\Timestamp;

use stdClass;

interface TimestampRepository
{
    public function getLastSession(int $sessionId): ?stdClass;

    public function store(int $sessionId, stdClass $timestamps): void;

    public function isFinished(int $sessionId): bool;

    public function finish(int $sessionId): void;

    /**
     * @return \stdClass[]
     */
    public function getNotFinished(): array;

    public function count(int $sessionId): int;

    public function getLastSessionId(): ?int;
}
