<?php

namespace SharedContext\Domain\ValueObject;

abstract class AbstractTimeInterval
{

    abstract protected function getStartTimeStamp(): float;

    abstract protected function getEndTimeStamp(): float;

    public function after(\DateTimeImmutable $time): bool
    {
        return $this->getStartTimeStamp() > $time->getTimestamp();
    }

    public function before(\DateTimeImmutable $time): bool
    {
        return $this->getEndTimeStamp() < $time->getTimestamp();
    }

    public function contain(\DateTimeImmutable $time): bool
    {
        return $this->getStartTimeStamp() <= $time->getTimestamp() && $this->getEndTimeStamp() >= $time->getTimestamp();
    }

    public function encompass(AbstractTimeInterval $other): bool
    {
        return $this->getStartTimeStamp() <= $other->getStartTimeStamp() && $this->getEndTimeStamp() >= $other->getEndTimeStamp();
    }

    protected function containTimeStamp($timeStamp)
    {
        return $this->getStartTimeStamp() <= $timeStamp && $this->getEndTimeStamp() >= $timeStamp;
    }

    public function intersectWith(AbstractTimeInterval $other): bool
    {
        return $this->containTimeStamp($other->getStartTimeStamp()) || $this->containTimeStamp($other->getEndTimeStamp()) || $other->encompass($this);
    }

    public function isUpcoming(): bool
    {
        return $this->getStartTimeStamp() > (new \DateTimeImmutable())->getTimestamp();
    }

    public function sameValueAs(AbstractTimeInterval $other): bool
    {
        return $this->getStartTimeStamp() === $other->getStartTimeStamp() && $this->getEndTimeStamp() === $other->getEndTimeStamp();
    }

}
