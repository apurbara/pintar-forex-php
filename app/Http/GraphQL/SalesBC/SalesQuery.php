<?php

namespace App\Http\GraphQL\SalesBC;

use App\Http\GraphQL\AppContext;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\TypeRegistry;

class SalesQuery extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'fields' => fn() => [
                'sales' => [
                    'type' => TypeRegistry::type(Query::class),
                    'args' => ['salesId' => Type::nonNull(Type::id())],
                    'resolve' => function ($root, $args, AppContext $app) {
                        $app->user = $app->user->authorizedAsSales($args['salesId']);
                        return TypeRegistry::type(Query::class);
                    }
                ],
            ],
        ]);
    }
    
}
