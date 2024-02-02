<?php

namespace App\Http\GraphQL\SalesBC;

use App\Http\Controllers\SalesBC\SalesRoleInterface;
use App\Http\Controllers\UserBC\ByPersonnel\PersonnelRoleInterface;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\AppContext;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use function app;

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
                        app()->singleton(SalesRoleInterface::class,
                                fn() => app(PersonnelRoleInterface::class)->authorizedAsSales($args['salesId']));
                        return TypeRegistry::type(Mutation::class);
                    }
                ],
            ],
        ]);
    }
}
