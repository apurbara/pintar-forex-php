<?php

namespace SharedContext\Domain\ValueObject;

readonly class HourlyTimeIntervalData
{

    public function __construct(public string $startTime)
    {
        
    }
}
