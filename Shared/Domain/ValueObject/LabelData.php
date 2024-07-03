<?php

namespace Shared\Domain\ValueObject;

readonly class LabelData
{

    public function __construct(public ?string $name, public ?string $description)
    {
        
    }
}
