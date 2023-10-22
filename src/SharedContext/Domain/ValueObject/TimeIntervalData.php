<?php

namespace SharedContext\Domain\ValueObject;

readonly class TimeIntervalData
{

    public function __construct(public ?string $startTime, public ?string $endTime)
    {
        
    }
}
