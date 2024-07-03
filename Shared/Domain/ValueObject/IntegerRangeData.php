<?php

namespace Shared\Domain\ValueObject;

readonly class IntegerRangeData
{

    public function __construct(public ?int $minimumValue, public ?int $maximumValue)
    {
        
    }
}
