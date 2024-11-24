<?php

namespace Manager\Application\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Manager\Application\Controllers\ClosingRequestController;
use Manager\Application\Controllers\CustomerAssignmentController;
use Manager\Application\Controllers\SalesController;
use Manager\Domain\Model\Manager;
use Resources\Infrastructure\GraphQL\ControllerToGraphqlFieldsMapper;
use Resources\Infrastructure\GraphQL\GraphqlInputRequest;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Resources\Infrastructure\GraphQL\ViewList\FilterInput;
use function app;

class Query extends ObjectType
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
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(CustomerAssignmentController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(ClosingRequestController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(SalesController::class),
            ...$this->customerAssignmentQuery(),
        ];
    }
    
    //
    private function customerAssignmentQuery(): array
    {
        return [
            'viewCustomerAssignmentCount' => [
                'type' => Type::int(),
                'args' => ['filters' => Type::listOf(TypeRegistry::inputType(FilterInput::class))],
                'resolve' => fn($root, $args) => (new CustomerAssignmentController())
                        ->viewCustomerAssignmentCount(app(Manager::class), new GraphqlInputRequest($args))
            ]
        ];
    }
}
