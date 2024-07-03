<?php

namespace Shared\Domain\ValueObject;

readonly class HourlyTimeIntervalData
{

    public function __construct(public string $startTime)
    {
    }
}
