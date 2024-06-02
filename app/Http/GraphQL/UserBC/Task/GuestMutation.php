<?php

namespace App\Http\GraphQL\UserBC\Task;

use App\Http\Controllers\UserBC\ByGuest\LoginController;
use App\Http\GraphQL\UserBC\Object\AdminLoginResponseGraph;
use App\Http\GraphQL\UserBC\Object\ManagerLoginResponseGraph;
use App\Http\GraphQL\UserBC\Object\SalesLoginResponseGraph;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\GraphqlInputRequest;
use Resources\Infrastructure\GraphQL\TypeRegistry;

class GuestMutation extends ObjectType
{

    public function __construct()
    {
        parent::__construct([
            'fields' => fn() => $this->fieldsDefinition(),
        ]);
    }

    protected function fieldsDefinition(): array
    {
        return [
            'adminLogin' => [
                'type' => TypeRegistry::objectType(AdminLoginResponseGraph::class),
                'args' => [
                    'email' => Type::nonNull(Type::string()),
                    'password' => Type::nonNull(Type::string()),
                ],
                'resolve' => fn($root, $args) => (new LoginController())
                        ->adminLogin(new GraphqlInputRequest($args))
            ],
            'managerLogin' => [
                'type' => TypeRegistry::objectType(ManagerLoginResponseGraph::class),
                'args' => [
                    'email' => Type::nonNull(Type::string()),
                    'password' => Type::nonNull(Type::string()),
                ],
                'resolve' => fn($root, $args) => (new LoginController())
                        ->managerLogin(new GraphqlInputRequest($args))
            ],
            'salesLogin' => [
                'type' => TypeRegistry::objectType(SalesLoginResponseGraph::class),
                'args' => [
                    'email' => Type::nonNull(Type::string()),
                    'password' => Type::nonNull(Type::string()),
                ],
                'resolve' => fn($root, $args) => (new LoginController())
                        ->salesLogin(new GraphqlInputRequest($args))
            ],
        ];
    }
}
