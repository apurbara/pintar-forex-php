<?php

namespace Manager\Application\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Manager\Application\Controllers\ClosingRequestController;
use Manager\Application\Controllers\FactFindingAssignmentController;
use Manager\Application\Controllers\GreetingAssignmentController;
use Manager\Application\Controllers\SalesController;
use Manager\Application\Controllers\StrikingAssignmentController;
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
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(GreetingAssignmentController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(FactFindingAssignmentController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(StrikingAssignmentController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(ClosingRequestController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(SalesController::class),
            ...$this->customerAssignmentQuery(),
        ];
    }
    
    //
    private function customerAssignmentQuery(): array
    {
        return [
            'viewGreetingAssignmentCount' => [
                'type' => Type::int(),
                'args' => ['filters' => Type::listOf(TypeRegistry::inputType(FilterInput::class))],
                'resolve' => fn($root, $args) => (new GreetingAssignmentController())
                        ->viewGreetingAssignmentCount(app(Manager::class), new GraphqlInputRequest($args))
            ],
            'viewFactFindingAssignmentCount' => [
                'type' => Type::int(),
                'args' => ['filters' => Type::listOf(TypeRegistry::inputType(FilterInput::class))],
                'resolve' => fn($root, $args) => (new FactFindingAssignmentController())
                        ->viewFactFindingAssignmentCount(app(Manager::class), new GraphqlInputRequest($args))
            ],
            'viewStrikingAssignmentCount' => [
                'type' => Type::int(),
                'args' => ['filters' => Type::listOf(TypeRegistry::inputType(FilterInput::class))],
                'resolve' => fn($root, $args) => (new StrikingAssignmentController())
                        ->viewStrikingAssignmentCount(app(Manager::class), new GraphqlInputRequest($args))
            ],
        ];
    }
}
