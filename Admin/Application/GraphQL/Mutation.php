<?php

namespace Admin\Application\GraphQL;

use Admin\Application\Controllers\AccountController;
use Admin\Application\Controllers\LoginController;
use Admin\Application\GraphQL\Object\AdminLoginResponse;
use Admin\Domain\Model\Admin;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\ControllerToGraphqlFieldsMapper;
use Resources\Infrastructure\GraphQL\CustomTypes\NoResponse;
use Resources\Infrastructure\GraphQL\GraphqlInputRequest;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use function app;

class Mutation extends ObjectType
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
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(AccountController::class),
            'login' => [
                'type' => TypeRegistry::objectType(AdminLoginResponse::class),
                'args' => [
                    'email' => Type::string(),
                    'password' => Type::string(),
                ],
                'resolve' => fn($root, $args) => app(LoginController::class)->login(new GraphqlInputRequest($args))
            ],
            'changePassword' => [
                'type' => TypeRegistry::type(NoResponse::class),
                'args' => [
                    'previousPassword' => Type::string(),
                    'newPassword' => Type::string(),
                ],
                'resolve' => fn($root, $args) => app(AccountController::class)
                ->changePassword(app(Admin::class), new GraphqlInputRequest($args))
            ],
        ];
    }
}
