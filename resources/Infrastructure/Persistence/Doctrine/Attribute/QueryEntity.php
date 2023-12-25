<?php

declare(strict_types=1);

namespace Resources\Infrastructure\Persistence\Doctrine\Attribute;

use Attribute;

#[Attribute]
final class QueryEntity
{

    public string $targetEntity;

    public function __construct(string $targetEntity)
    {
        $this->targetEntity = $targetEntity;
    }
}
