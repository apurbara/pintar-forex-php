<?php

namespace App\Http\GraphQL\ManagerBC;

use App\Http\Controllers\ManagerBC\AssignedCustomerController;
use App\Http\Controllers\ManagerBC\ClosingRequestController;
use App\Http\Controllers\ManagerBC\ManagerRoleInterface;
use App\Http\Controllers\ManagerBC\RecycleRequestController;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\ControllerToGraphqlFieldsMapper;
use Resources\Infrastructure\GraphQL\CustomTypes\NoResponse;
use Resources\Infrastructure\GraphQL\GraphqlInputRequest;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use function app;

class ManagerMutation extends ObjectType
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
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(ClosingRequestController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(RecycleRequestController::class),
            'assignedMultipleCustomerToMultipleSales' => [
                'type' => TypeRegistry::type(NoResponse::class),
                'args' => [
                    'salesList' => Type::listOf(Type::id()),
                    'customerList' => Type::listOf(Type::id()),
                ],
                'resolve' => fn($root, $args) => app(AssignedCustomerController::class)
                        ->assignedMultipleCustomerToMultipleSales(app(ManagerRoleInterface::class),
                                new GraphqlInputRequest($args))
            ],
        ];
    }
}
