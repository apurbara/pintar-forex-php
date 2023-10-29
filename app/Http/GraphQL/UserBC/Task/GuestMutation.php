<?php

namespace App\Http\GraphQL\UserBC\Task;

use App\Http\Controllers\UserBC\ByGuest\LoginController;
use App\Http\Controllers\UserRole\PersonnelRole;
use App\Http\GraphQL\AppContext;
use App\Http\GraphQL\GraphqlInputRequest;
use App\Http\GraphQL\UserBC\Object\AdminLoginResponseGraph;
use App\Http\GraphQL\UserBC\Object\PersonnelLoginResponseGraph;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
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
            'personnelLogin' => [
                'type' => TypeRegistry::objectType(PersonnelLoginResponseGraph::class),
                'args' => [
                    'email' => Type::nonNull(Type::string()),
                    'password' => Type::nonNull(Type::string()),
                ],
                'resolve' => function($root, $args, AppContext $app){
                    $result = (new LoginController())->personnelLogin(new GraphqlInputRequest($args));
                    $app->user = new PersonnelRole($result['id']);
                    return $result;
                }
            ],
        ];
    }
}
