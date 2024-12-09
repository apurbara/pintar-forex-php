<?php

namespace Company\Application\GraphQL;

use Company\Application\Controllers\CityController;
use Company\Application\Controllers\ClosingRequestController;
use Company\Application\Controllers\CommonSalesMetricController;
use Company\Application\Controllers\CompanyMetricController;
use Company\Application\Controllers\CustomerController;
use Company\Application\Controllers\CustomerJourneyController;
use Company\Application\Controllers\CustomerVerificationController;
use Company\Application\Controllers\FactFinderMetricController;
use Company\Application\Controllers\FactFindingAssignmentController;
use Company\Application\Controllers\GreeterMetricController;
use Company\Application\Controllers\GreetingAssignmentController;
use Company\Application\Controllers\ManagerController;
use Company\Application\Controllers\ProvinceController;
use Company\Application\Controllers\SalesActivityController;
use Company\Application\Controllers\SalesController;
use Company\Application\Controllers\SalesPerformanceMetricController;
use Company\Application\Controllers\SalesRankController;
use Company\Application\Controllers\StrikingAssignmentController;
use Company\Domain\Model\CompanyUser;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
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
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(CustomerJourneyController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(CustomerVerificationController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(SalesActivityController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(ManagerController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(SalesController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(CustomerController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(GreetingAssignmentController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(FactFindingAssignmentController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(StrikingAssignmentController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(ClosingRequestController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(CompanyMetricController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(SalesRankController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(CommonSalesMetricController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(SalesPerformanceMetricController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(ProvinceController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(CityController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(GreeterMetricController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(FactFinderMetricController::class),
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
                        ->viewGreetingAssignmentCount(app(CompanyUser::class), new GraphqlInputRequest($args))
            ],
            'viewFactFindingAssignmentCount' => [
                'type' => Type::int(),
                'args' => ['filters' => Type::listOf(TypeRegistry::inputType(FilterInput::class))],
                'resolve' => fn($root, $args) => (new FactFindingAssignmentController())
                        ->viewFactFindingAssignmentCount(app(CompanyUser::class), new GraphqlInputRequest($args))
            ],
            'viewStrikingAssignmentCount' => [
                'type' => Type::int(),
                'args' => ['filters' => Type::listOf(TypeRegistry::inputType(FilterInput::class))],
                'resolve' => fn($root, $args) => (new StrikingAssignmentController())
                        ->viewStrikingAssignmentCount(app(CompanyUser::class), new GraphqlInputRequest($args))
            ],
        ];
    }
}
