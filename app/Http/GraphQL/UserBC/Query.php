<?php

namespace App\Http\GraphQL\UserBC;

use App\Http\GraphQL\UserBC\Task\PersonnelQuery;
use GraphQL\Type\Definition\ObjectType;
use Resources\Infrastructure\GraphQL\TypeRegistry;

class Query extends ObjectType
{

    public function __construct()
    {
        parent::__construct([
            'fields' => fn() => $this->fieldDefinition(),
        ]);
    }

    protected function fieldDefinition(): array
    {
        return [
            'byPersonnel' => [
                'type' => TypeRegistry::type(PersonnelQuery::class),
                'resolve' => fn() => TypeRegistry::type(PersonnelQuery::class),
            ],
        ];
    }
}
