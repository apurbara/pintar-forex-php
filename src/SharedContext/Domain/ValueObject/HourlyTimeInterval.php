<?php

namespace SharedContext\Domain\ValueObject;

use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;

#[Embeddable]
class HourlyTimeInterval extends AbstractTimeInterval
{

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected ?DateTimeImmutable $startTime;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected ?DateTimeImmutable $endTime;

    public function __construct(HourlyTimeIntervalData $data)
    {
        $startTime = new \DateTimeImmutable($data->startTime);
        $this->startTime = $startTime->setTime($startTime->format('H'), 0);
        $this->endTime = $this->startTime->add(new DateInterval('PT1H'));
    }

    protected function getEndTimeStamp(): float
    {
        return $this->endTime->getTimestamp();
    }

    protected function getStartTimeStamp(): float
    {
        return $this->startTime->getTimestamp();
    }
}
