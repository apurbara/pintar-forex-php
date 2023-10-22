<?php

namespace SharedContext\Domain\ValueObject;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Resources\Exception\RegularException;

#[Embeddable]
class DateInterval extends AbstractTimeInterval
{

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected ?DateTimeImmutable $startDate;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected ?DateTimeImmutable $endDate;

    protected function getStartTimeStamp(): float
    {
        return isset($this->startDate) ? $this->startDate->getTimestamp() : -INF;
    }

    protected function getEndTimeStamp(): float
    {
        return isset($this->endDate) ? $this->endDate->setTime(23, 59, 59)->getTimestamp() : INF;
    }

    protected function setStartDate($startDate): void
    {
        $this->startDate = $startDate ? (new DateTimeImmutable($startDate))->setTime(0, 0) : null;
    }

    protected function setEndDate($endDate): void
    {
        $this->endDate = $endDate ? (new DateTimeImmutable($endDate))->setTime(0, 0) : null;
    }

    public function __construct(DateIntervalData $timeIntervalData)
    {
        $this->setStartDate($timeIntervalData->startDate);
        $this->setEndDate($timeIntervalData->endDate);
        if ($this->getEndTimeStamp() < $this->getStartTimeStamp()) {
            throw RegularException::badRequest('bad request: end date must be bigger than or equals start date');
        }
    }
}
