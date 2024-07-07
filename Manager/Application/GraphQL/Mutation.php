<?php

namespace Manager\Application\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Manager\Application\Controllers\AccountController;
use Manager\Application\Controllers\ClosingRequestController;
use Manager\Application\Controllers\CustomerAssignmentController;
use Manager\Application\Controllers\LoginController;
use Manager\Application\Controllers\RecycleRequestController;
use Manager\Application\GraphQL\Object\ManagerLoginResponse;
use Manager\Domain\Model\Manager;
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
            ...$this->login(),
            ...$this->accountMutation(),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(AccountController::class),
            ...$this->customerAssignmentMutation(),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(CustomerAssignmentController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(ClosingRequestController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(RecycleRequestController::class),
        ];
    }

    protected function customerAssignmentMutation(): array
    {
        return [
            'assignMultipleCustomerToMultipleSales' => [
                'type' => TypeRegistry::type(NoResponse::class),
                'args' => [
                    'salesList' => Type::listOf(Type::id()),
                    'customerList' => Type::listOf(Type::id()),
                    'distributionStrategy' => Type::string(),
                    'initiateSchedules' => Type::boolean(),
                ],
                'resolve' => fn($root, $args) => app(CustomerAssignmentController::class)
                        ->assignedMultipleCustomerToMultipleSales(app(Manager::class), new GraphqlInputRequest($args))
            ],
        ];
    }

    protected function login(): array
    {
        return [
            'login' => [
                'type' => TypeRegistry::objectType(ManagerLoginResponse::class),
                'args' => [
                    'email' => Type::string(),
                    'password' => Type::string(),
                ],
                'resolve' => fn($root, $args) => app(LoginController::class)->login(new GraphqlInputRequest($args))
            ],
        ];
    }

    protected function accountMutation(): array
    {
        return [
            'changePassword' => [
                'type' => TypeRegistry::type(NoResponse::class),
                'args' => [
                    'previousPassword' => Type::string(),
                    'newPassword' => Type::string(),
                ],
                'resolve' => fn($root, $args) => app(AccountController::class)
                        ->changePassword(app(Manager::class), new GraphqlInputRequest($args))
            ],
        ];
    }
}
