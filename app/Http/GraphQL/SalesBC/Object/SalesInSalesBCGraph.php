<?php

namespace App\Http\GraphQL\SalesBC\Object;

use App\Http\Controllers\SalesBC\AssignedCustomerController;
use App\Http\Controllers\SalesBC\SalesActivityScheduleController;
use App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomer\SalesActivityScheduleInSalesBCGraph;
use App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomerInSalesBCGraph;
use Resources\Infrastructure\GraphQL\AppContext;
use Resources\Infrastructure\GraphQL\GraphqlInputRequest;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use Resources\Infrastructure\GraphQL\InputListSchema;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Sales\Domain\Model\Personnel\Sales;

class SalesInSalesBCGraph extends GraphqlObjectType
{
    
    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
            'assignedCustomers' => [
                'type' => TypeRegistry::paginationType(AssignedCustomerInSalesBCGraph::class),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) =>
                (new AssignedCustomerController())->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'salesActivitySchedules' => [
                'type' => TypeRegistry::paginationType(SalesActivityScheduleInSalesBCGraph::class),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) =>
                (new SalesActivityScheduleController())->viewList($app->user, new GraphqlInputRequest($args))
            ],
        ];
    }

    protected function getClassMetadata(): string
    {
        return Sales::class;
    }
}
