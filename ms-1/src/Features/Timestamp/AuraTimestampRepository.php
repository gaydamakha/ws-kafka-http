<?php

declare(strict_types=1);

namespace Gaydamakha\WsKafkaHttp\Ms1\Features\Timestamp;

use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\UpdateInterface;
use Aura\SqlQuery\QueryFactory;
use Aura\SqlQuery\QueryInterface;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;
use PDO;
use stdClass;
use TypeError;
use UnexpectedValueException;

class AuraTimestampRepository implements TimestampRepository
{
    private PDO $pdo;
    private QueryFactory $queryFactory;
    private static string $ANSI_SQL_DATETIME_FORMAT = 'Y-m-d H:i:s.u';

    public function __construct(PDO $pdo, QueryFactory $queryFactory)
    {
        $this->pdo = $pdo;
        $this->queryFactory = $queryFactory;
    }

    public function store(int $sessionId, stdClass $timestamps): void
    {
        $query = $this->queryFactory->newInsert()
            ->into('timestamps')
            ->cols([
                'session_id' => $sessionId,
                'ms1_timestamp' => self::fromDateTime(new DateTime($timestamps->MC1_timestamp)),
                'ms2_timestamp' => self::fromDateTime(new DateTime($timestamps->MC2_timestamp)),
                'ms3_timestamp' => self::fromDateTime(new DateTime($timestamps->MC3_timestamp)),
                'end_timestamp' => self::fromDateTime(new DateTime($timestamps->end_timestamp)),
            ]);

        $this->executeInsert($query);
    }

    public function count(int $sessionId): int
    {
        $query = $this->queryFactory
            ->newSelect()
            ->from('timestamps')
            ->cols(['count(*) as cnt'])
            ->where("session_id = $sessionId");

        $data = $this->fetch($query);

        return self::asInt($data['cnt']);
    }

    /**
     * @param SelectInterface $query
     * @return string[]|null
     */
    protected function fetch(SelectInterface $query): ?array
    {
        if ($query->getLimit() === 0) {
            $query->limit(2);
        }

        $statement = $this->pdo->prepare($query->getStatement());
        $executeResult = $statement->execute($query->getBindValues());

        if (!$executeResult) {
            throw new UnexpectedValueException('$statement->execute() returned false in query ');
        }

        $fetchResult = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (!$fetchResult) {
            throw new UnexpectedValueException('$statement->fetch() returned false in query ');
        }

        switch (count($fetchResult)) {
            case 0:
                return null;
            case 1:
                return $fetchResult[0];
            default:
                throw new UnexpectedValueException('$statement->fetch() returned too many results in query ');
        }
    }

    /**
     * @param QueryInterface $query
     * @return string[][]
     */
    protected function fetchAll(QueryInterface $query): array
    {
        $statement = $this->pdo->prepare($query->getStatement());
        $result = $statement->execute($query->getBindValues());
        if ($result !== true) {
            throw new UnexpectedValueException('Cannot fetch');
        }
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param mixed $value
     * @param int|null $default
     * @return int|null
     */
    public static function asInt($value, int $default = null): ?int
    {
        if ($value === null) {
            return $default;
        }

        if (is_int($value)) {
            return $value;
        }

        if (!is_string($value)) {
            throw new TypeError('Argument 1 passed to asInt() must be of the type integer or string, ' . gettype($value) . ' given');
        }

        if (preg_match('%^-?[0-9]+$%', $value) !== 1) {
            throw new InvalidArgumentException('Argument 1 passed to asInt() must be an integer-like value, ' . $value . ' given');
        }

        return (int)$value;
    }

    protected function executeInsert(InsertInterface $insert): int
    {
        $statement = $this->pdo->prepare($insert->getStatement());
        $result = $statement->execute($insert->getBindValues());
        if ($result !== true) {
            throw new UnexpectedValueException('Cannot insert: ' . print_r($this->pdo->errorInfo()));
        }
        return (int)$this->pdo->lastInsertId();
    }

    public static function fromDateTime(?DateTimeInterface $dateTime): ?string
    {
        if ($dateTime === null) {
            return null;
        }

        return (clone $dateTime)->setTimezone(new DateTimeZone('UTC'))->format(self::$ANSI_SQL_DATETIME_FORMAT);
    }

    public function getLastSessionId(): ?int
    {
        $query = $this->queryFactory
            ->newSelect()
            ->distinct()
            ->from('timestamps')
            ->cols(['session_id'])
            ->orderBy(['end_timestamp DESC']);

        $result = $this->fetchAll($query);

        return self::asInt($result[0]['session_id']);
    }

    public function isFinished(int $sessionId): bool
    {
        $query = $this->queryFactory
            ->newSelect()
            ->from('timestamps')
            ->cols(['finished'])
            ->where("session_id = $sessionId")
            ->orderBy(['end_timestamp DESC']);

        $result = $this->fetchAll($query);

        return self::asInt($result[0]['finished']) === 1;
    }

    public function finish(int $sessionId): void
    {
        $query = $this->queryFactory->newUpdate()
            ->table('timestamps')
            ->cols(['finished' => 1])
            ->where("session_id = :session_id")
            ->bindValue('session_id', $sessionId);

        $this->executeUpdate($query);
    }

    protected function executeUpdate(UpdateInterface $update): int
    {
        $statement = $this->pdo->prepare($update->getStatement());
        $result = $statement->execute($update->getBindValues());
        if ($result !== true) {
            throw new UnexpectedValueException('Cannot update');
        }
        return $statement->rowCount();
    }

    public function getNotFinished(): array
    {
        $query = $this->queryFactory
            ->newSelect()
            ->from('timestamps')
            ->cols(['*'])
            ->where('finished = 0')
            ->orderBy(['end_timestamp DESC']);

        $results = $this->fetchAll($query);

        $timestamps = [];
        foreach ($results as $result) {
            $timestamps[] = self::fromDb($result);
        }

        return $timestamps;
    }

    public static function fromDb(array $item): stdClass
    {
        $timestamp = new stdClass();
        $timestamp->session_id = self::asInt($item['session_id']);
        $timestamp->MC1_timestamp = (new DateTime($item['ms1_timestamp']))->format(DATE_FORMAT);
        $timestamp->MC2_timestamp = (new DateTime($item['ms2_timestamp']))->format(DATE_FORMAT);
        $timestamp->MC3_timestamp = (new DateTime($item['ms3_timestamp']))->format(DATE_FORMAT);
        $timestamp->end_timestamp = (new DateTime($item['end_timestamp']))->format(DATE_FORMAT);

        return $timestamp;
    }

    public function getLastSession(int $sessionId): ?stdClass
    {
        $query = $this->queryFactory
            ->newSelect()
            ->distinct()
            ->from('timestamps')
            ->cols(['*'])
            ->orderBy(['end_timestamp DESC']);

        $result = $this->fetchAll($query);

        if (empty($result)) {
            return null;
        }

        return self::fromDb($result[0]);
    }
}
