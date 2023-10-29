<?php

namespace App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomer;

use App\Http\Controllers\CompanyBC\InCompany\SalesActivityController;
use App\Http\Controllers\SalesBC\AssignedCustomerController;
use App\Http\GraphQL\AppContext;
use App\Http\GraphQL\CompanyBC\Object\SalesActivityGraph;
use App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomerInSalesBCGraph;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule;

class SalesActivityScheduleInSalesBCGraph extends GraphqlObjectType
{

    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
            'salesActivity' => [
                'type' => TypeRegistry::objectType(SalesActivityGraph::class),
                'resolve' => fn($root, $args, AppContext $app) =>
                (new SalesActivityController())->viewDetail($app->user, $root['SalesActivity_id'])
            ],
            'assignedCustomer' => [
                'type' => TypeRegistry::objectType(AssignedCustomerInSalesBCGraph::class),
                'resolve' => fn($root, $args, AppContext $app) =>
                (new AssignedCustomerController())->viewDetail($app->user, $root['SalesActivity_id'])
            ],
        ];
    }

    protected function getClassMetadata(): string
    {
        return SalesActivitySchedule::class;
    }
}
