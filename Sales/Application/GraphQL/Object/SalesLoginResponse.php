<?php

namespace Sales\Application\GraphQL\Object;

use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use Sales\Domain\Model\Sales;

class SalesLoginResponse extends GraphqlObjectType
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
