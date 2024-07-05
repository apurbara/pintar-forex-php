<?php

namespace Sales\Application\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\AppContext;
use Resources\Infrastructure\GraphQL\ControllerToGraphqlFieldsMapper;
use Resources\Infrastructure\GraphQL\GraphqlInputRequest;
use Resources\Infrastructure\GraphQL\InputListSchema;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Resources\Infrastructure\GraphQL\ViewList\FilterInput;
use Sales\Application\Controllers\ClosingRequestController;
use Sales\Application\Controllers\CustomerAssignmentController;
use Sales\Application\Controllers\RecycleRequestController;
use Sales\Application\Controllers\SalesActivityReportController;
use Sales\Application\Controllers\SalesActivityScheduleController;
use Sales\Application\Controllers\VerificationReportController;
use Sales\Application\GraphQL\Object\SalesActivityScheduleSummaryInSalesBCGraph;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
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
            'totalCustomerAssignment' => [
                'type' => Type::int(),
                'args' => ['filters' => Type::listOf(TypeRegistry::inputType(FilterInput::class)),],
                'resolve' => fn($root, $args, AppContext $app) => (new CustomerAssignmentController())
                        ->totalCustomerAssignment(app(Sales::class), new GraphqlInputRequest($args))
            ],
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(ClosingRequestController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(RecycleRequestController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(SalesActivityReportController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(SalesActivityScheduleController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(VerificationReportController::class),
            ...$this->salesActivityScheduleCustomQuery(),
        ];
    }
    
    //
    private function salesActivityScheduleCustomQuery(): array
    {
        return [
            'salesActivityScheduleSummaryList' => [
                'type' => Type::listOf(TypeRegistry::objectType(SalesActivityScheduleSummaryInSalesBCGraph::class)),
                'args' => InputListSchema::allListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new SalesActivityScheduleController())
                        ->salesActivityScheduleSummaryList(app(Sales::class),
                                new GraphqlInputRequest($args))
            ],
            'totalSalesActivitySchedule' => [
                'type' => Type::int(),
                'args' => ['filters' => Type::listOf(TypeRegistry::inputType(FilterInput::class)),],
                'resolve' => fn($root, $args, AppContext $app) => (new SalesActivityScheduleController())
                        ->totalSalesActivitySchedule(app(Sales::class), new GraphqlInputRequest($args))
            ],
            'viewAllNonInitialSchedulesInMonth' => [
                'type' => Type::listOf(TypeRegistry::objectType(SalesActivitySchedule::class)),
                'args' => [
                    'year' => Type::int(),
                    'month' => Type::int(),
                ],
                'resolve' => fn($root, $args, AppContext $app) => (new SalesActivityScheduleController())
                        ->viewAllNonInitialSchedulesInMonth(app(Sales::class), new GraphqlInputRequest($args))
            ],
        ];
    }
}
