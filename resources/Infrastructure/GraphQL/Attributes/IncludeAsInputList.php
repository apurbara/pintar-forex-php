<?php

declare(strict_types=1);

namespace Resources\Infrastructure\GraphQL\Attributes;

use Attribute;

#[Attribute]
final class IncludeAsInputList
{
    public string $targetEntity;

    public function __construct(string $targetEntity)
    {
        $this->targetEntity = $targetEntity;
    }
}
