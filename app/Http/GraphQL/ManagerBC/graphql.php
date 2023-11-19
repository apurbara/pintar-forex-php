<?php

namespace App\Http\GraphQL\ManagerBC;

// Test this using following command
// php -S localhost:8080 ./graphql.php
//require_once __DIR__ . '/../../vendor/autoload.php';


use App\Http\GraphQL\AppContext;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use Resources\Infrastructure\GraphQL\TypeRegistry;

$query = new ObjectType([
    'name' => 'Query',
    'fields' => fn() => [
        'manager' => [
            'type' => TypeRegistry::type(ManagerQuery::class),
            'args' => ['managerId' => Type::nonNull(Type::id())],
            'resolve' => function($root, $args, AppContext $appContext){
                $appContext->user = $appContext->user->authorizeAsManager($args['managerId']);
                return TypeRegistry::type(ManagerQuery::class);
            }
        ],
    ],
]);

$mutation = new ObjectType([
    'name' => 'Mutation',
    'fields' => fn() => [
        'manager' => [
            'type' => TypeRegistry::type(ManagerMutation::class),
            'args' => ['managerId' => Type::nonNull(Type::id())],
            'resolve' => function($root, $args, AppContext $appContext){
                $appContext->user = $appContext->user->authorizeAsManager($args['managerId']);
                return TypeRegistry::type(ManagerMutation::class);
            }
        ],
    ],
]);
    
$schema = new Schema([
    'query' => $query,
    'mutation' => $mutation,
    'typeLoader' => static fn($name) => TypeRegistry::type($name),
]);

return require __DIR__ . '/../executeGraphqlQuery.php';
