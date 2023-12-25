<?php

namespace App\Http\GraphQL\ManagerBC;

// Test this using following command
// php -S localhost:8080 ./graphql.php
//require_once __DIR__ . '/../../vendor/autoload.php';


use App\Http\Controllers\ManagerBC\ManagerRoleInterface;
use App\Http\Controllers\UserBC\ByPersonnel\PersonnelRoleInterface;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use Resources\Infrastructure\GraphQL\AppContext;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use function app;
use function base_path;

$query = new ObjectType([
    'name' => 'ManagerQuery',
    'fields' => fn() => [
        'manager' => [
            'type' => TypeRegistry::type(ManagerQuery::class),
            'args' => ['managerId' => Type::nonNull(Type::id())],
            'resolve' => function($root, $args, AppContext $appContext){
                app()->singleton(ManagerRoleInterface::class, fn() => app(PersonnelRoleInterface::class)->authorizeAsManager($args['managerId']));
                return TypeRegistry::type(ManagerQuery::class);
            }
        ],
    ],
]);

$mutation = new ObjectType([
    'name' => 'ManagerMutation',
    'fields' => fn() => [
        'manager' => [
            'type' => TypeRegistry::type(ManagerMutation::class),
            'args' => ['managerId' => Type::nonNull(Type::id())],
            'resolve' => function($root, $args, AppContext $appContext){
                app()->singleton(ManagerRoleInterface::class, fn() => app(PersonnelRoleInterface::class)->authorizeAsManager($args['managerId']));
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

return require base_path() . '/resources/Infrastructure/GraphQL/executeGraphqlQuery.php';
