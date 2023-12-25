<?php

namespace App\Http\GraphQL\SalesBC;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\AppContext;
use Resources\Infrastructure\GraphQL\TypeRegistry;

class SalesMutation extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'fields' => fn() => [
                'sales' => [
                    'type' => TypeRegistry::type(Mutation::class),
                    'args' => ['salesId' => Type::nonNull(Type::id())],
                    'resolve' => function ($root, $args, AppContext $app) {
                        $app->user = $app->user->authorizedAsSales($args['salesId']);
                        return TypeRegistry::type(Mutation::class);
                    }
                ],
            ],
        ]);
    }
    
}
