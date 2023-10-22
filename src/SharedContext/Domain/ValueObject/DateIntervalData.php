<?php

namespace SharedContext\Domain\ValueObject;

readonly class DateIntervalData
{

    public function __construct(public ?string $startDate, public ?string $endDate)
    {
        
    }
}
