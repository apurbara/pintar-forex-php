<?php

namespace App\Http\GraphQL\UserBC\Object;

use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use User\Domain\Model\Sales;

class SalesLoginResponseGraph extends GraphqlObjectType
{
    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
            'token' => Type::string(),
        ];
    }

    protected function getClassMetadata(): string
    {
        return Sales::class;
    }
}
