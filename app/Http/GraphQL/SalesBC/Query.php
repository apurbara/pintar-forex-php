<?php

namespace App\Http\GraphQL\SalesBC;

use App\Http\Controllers\SalesBC\AssignedCustomerController;
use App\Http\Controllers\SalesBC\ClosingRequestController;
use App\Http\Controllers\SalesBC\RecycleRequestController;
use App\Http\Controllers\SalesBC\SalesActivityReportController;
use App\Http\Controllers\SalesBC\SalesActivityScheduleController;
use App\Http\Controllers\SalesBC\SalesRoleInterface;
use App\Http\Controllers\SalesBC\VerificationReportController;
use App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomer\SalesActivityScheduleSummaryInSalesBCGraph;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\AppContext;
use Resources\Infrastructure\GraphQL\ControllerToGraphqlFieldsMapper;
use Resources\Infrastructure\GraphQL\GraphqlInputRequest;
use Resources\Infrastructure\GraphQL\InputListSchema;
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
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(AssignedCustomerController::class),
            'totalCustomerAssignment' => [
                'type' => Type::int(),
                'args' => ['filters' => Type::listOf(TypeRegistry::inputType(FilterInput::class)),],
                'resolve' => fn($root, $args, AppContext $app) => (new AssignedCustomerController())
                        ->totalCustomerAssignment(app(SalesRoleInterface::class), new GraphqlInputRequest($args))
            ],
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(ClosingRequestController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(RecycleRequestController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(SalesActivityReportController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(SalesActivityScheduleController::class),
            'salesActivityScheduleSummaryList' => [
                'type' => Type::listOf(TypeRegistry::objectType(SalesActivityScheduleSummaryInSalesBCGraph::class)),
                'args' => InputListSchema::allListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new SalesActivityScheduleController())
                        ->salesActivityScheduleSummaryList(app(SalesRoleInterface::class),
                                new GraphqlInputRequest($args))
            ],
            'totalSalesActivitySchedule' => [
                'type' => Type::int(),
                'args' => ['filters' => Type::listOf(TypeRegistry::inputType(FilterInput::class)),],
                'resolve' => fn($root, $args, AppContext $app) => (new SalesActivityScheduleController())
                        ->totalSalesActivitySchedule(app(SalesRoleInterface::class), new GraphqlInputRequest($args))
            ],
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(VerificationReportController::class),
        ];
    }
}
