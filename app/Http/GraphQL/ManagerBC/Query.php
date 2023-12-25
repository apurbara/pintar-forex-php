<?php

namespace App\Http\GraphQL\ManagerBC;

use GraphQL\Type\Definition\ObjectType;
use Resources\Infrastructure\GraphQL\ControllerToGraphqlFieldsMapper;

class Query extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'fields' => fn() => $this->fieldDefinition()
        ]);
    }
    
    protected function fieldDefinition(): array
    {
        return [
            ...ControllerToGraphqlFieldsMapper::mapQueryFields($controllerMetadata),
        ];
    }
}
