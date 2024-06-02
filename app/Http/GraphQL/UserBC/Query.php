<?php

namespace App\Http\GraphQL\UserBC;

use App\Http\GraphQL\UserBC\Task\ManagerQuery;
use App\Http\GraphQL\UserBC\Task\SalesQuery;
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
            'byManager' => [
                'type' => TypeRegistry::type(ManagerQuery::class),
                'resolve' => fn() => TypeRegistry::type(ManagerQuery::class),
            ],
            'bySales' => [
                'type' => TypeRegistry::type(SalesQuery::class),
                'resolve' => fn() => TypeRegistry::type(SalesQuery::class),
            ],
        ];
    }
}
