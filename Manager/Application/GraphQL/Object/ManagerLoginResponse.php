<?php

namespace Manager\Application\GraphQL\Object;

use GraphQL\Type\Definition\Type;
use Manager\Domain\Model\Manager;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;

class ManagerLoginResponse extends GraphqlObjectType
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
        return Manager::class;
    }
}
