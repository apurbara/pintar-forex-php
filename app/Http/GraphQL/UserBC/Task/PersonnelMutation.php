<?php

namespace App\Http\GraphQL\UserBC\Task;

use App\Http\Controllers\UserBC\ByPersonnel\AccountController;
use App\Http\Controllers\UserBC\ByPersonnel\PersonnelRoleInterface;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\ControllerToGraphqlFieldsMapper;
use Resources\Infrastructure\GraphQL\CustomTypes\NoResponse;
use Resources\Infrastructure\GraphQL\GraphqlInputRequest;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use function app;

class PersonnelMutation extends ObjectType
{

    public function __construct()
    {
        parent::__construct([
            'fields' => fn() => $this->fieldDefinitions(),
        ]);
    }

    //
    protected function fieldDefinitions(): array
    {
        return [
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(AccountController::class),
            'changePassword' => [
                'type' => TypeRegistry::type(NoResponse::class),
                'args' => [
                    'previousPassword' => Type::string(),
                    'newPassword' => Type::string(),
                ],
                'resolve' => fn($root, $args) => app(AccountController::class)
                        ->changePassword(app(PersonnelRoleInterface::class), new GraphqlInputRequest($args))
            ],
        ];
    }
}
