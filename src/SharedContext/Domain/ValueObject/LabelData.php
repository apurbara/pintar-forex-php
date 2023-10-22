<?php

namespace SharedContext\Domain\ValueObject;

readonly class LabelData
{

    public function __construct(public ?string $title, public ?string $description)
    {
        
    }
}
