<?php

namespace App\Http\GraphQL\ManagerBC\Object;

use Company\Domain\Model\Personnel\Manager;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;

class ManagerInManagerBCGraph extends GraphqlObjectType
{
    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
        ];
    }
    
    protected function getClassMetadata(): string
    {
        return Manager::class;
    }
}
