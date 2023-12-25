<?php

declare(strict_types=1);

namespace Resources\Infrastructure\GraphQL\Attributes;

use Attribute;

#[Attribute]
final class GraphqlMapableController
{

    public readonly string $entity;
    public readonly string $responseType;

    public function __construct(string $entity, string $responseType)
    {
        $this->entity = $entity;
        $this->responseType = $responseType;
    }
}
