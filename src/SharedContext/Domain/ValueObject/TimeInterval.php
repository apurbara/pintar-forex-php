<?php

namespace SharedContext\Domain\ValueObject;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Resources\Exception\RegularException;

#[Embeddable]
class TimeInterval extends AbstractTimeInterval
{

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected ?DateTimeImmutable $startTime;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected ?DateTimeImmutable $endTime;

    public function __construct(TimeIntervalData $timeIntervalData)
    {
        $this->startTime = $timeIntervalData->startTime ? new DateTimeImmutable($timeIntervalData->startTime) : null;
        $this->endTime = $timeIntervalData->endTime ? new DateTimeImmutable($timeIntervalData->endTime) : null;
        if ($this->getEndTimeStamp() <= $this->getStartTimeStamp()) {
            throw RegularException::badRequest('bad request: end time must be bigger than start time');
        }
    }

    protected function getStartTimeStamp(): float
    {
        return isset($this->startTime) ? $this->startTime->getTimestamp() : -INF;
    }

    protected function getEndTimeStamp(): float
    {
        return isset($this->endTime) ? $this->endTime->getTimestamp() : INF;
    }
}
