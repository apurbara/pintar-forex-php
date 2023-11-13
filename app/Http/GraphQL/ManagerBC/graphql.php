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

$managerQuery = new ObjectType([
    'name' => 'managerQuery',
    'fields' => fn() => [
        'type' => TypeRegistry::type(Query::class),
        'args' => ['managerId' => Type::nonNull(Type::id())],
        'resolve' => function($root, $args, AppContext $appContext){
            $appContext->user->authorizeAsManager($args['managerId']);
            return TypeRegistry::type(Query::class);
        }
    ],
]);

$managerMutation = new ObjectType([
    'name' => 'managerMutation',
    'fields' => fn() => [
        'type' => TypeRegistry::type(Mutation::class),
        'args' => ['managerId' => Type::nonNull(Type::id())],
        'resolve' => function($root, $args, AppContext $appContext){
            $appContext->user->authorizeAsManager($args['managerId']);
            return TypeRegistry::type(Mutation::class);
        }
    ],
]);
    
$schema = new Schema([
    'query' => TypeRegistry::type($managerQuery),
    'mutation' => TypeRegistry::type($managerMutation),
    'typeLoader' => static fn($name) => TypeRegistry::type($name),
]);

return require __DIR__ . '/../executeGraphqlQuery.php';
