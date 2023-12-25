<?php

namespace App\Http\GraphQL\SalesBC\Object\Sales;

use App\Http\Controllers\SalesBC\ClosingRequestController;
use App\Http\Controllers\SalesBC\RecycleRequestController;
use App\Http\Controllers\SalesBC\SalesActivityScheduleController;
use App\Http\GraphQL\CompanyBC\Object\CustomerJourneyGraph;
use App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomer\ClosingRequestInSalesBCGraph;
use App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomer\CustomerInSalesBCGraph;
use App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomer\RecycleRequestInSalesBCGraph;
use App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomer\SalesActivityScheduleInSalesBCGraph;
use Company\Domain\Model\CustomerJourney;
use Resources\Infrastructure\GraphQL\AppContext;
use Resources\Infrastructure\GraphQL\GraphqlInputRequest;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use Resources\Infrastructure\GraphQL\InputListSchema;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;

class AssignedCustomerInSalesBCGraph extends GraphqlObjectType
{
    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
            'customer' => [
                'type' => TypeRegistry::objectType(CustomerInSalesBCGraph::class),
                'resolve' => fn ($root) => $this->buildDoctrineRepository(Customer::class)->fetchOneById($root['Customer_id'])
            ],
            'customerJourney' => [
                'type' => TypeRegistry::objectType(CustomerJourneyGraph::class),
                'resolve' => fn ($root) => isset($root['CustomerJourney_id']) ? $this->buildDoctrineRepository(CustomerJourney::class)->fetchOneById($root['CustomerJourney_id']) : null
            ],
            'schedules' => [
                'type' => TypeRegistry::paginationType(SalesActivityScheduleInSalesBCGraph::class),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => function ($root, $args, AppContext $app) {
                    $args['filters'][] = ['column' => 'SalesActivitySchedule.AssignedCustomer_id', 'value' => $root['id']];
                    return (new SalesActivityScheduleController())->viewList($app->user, new GraphqlInputRequest($args));
                }
            ],
            'closingRequests' => [
                'type' => TypeRegistry::paginationType(ClosingRequestInSalesBCGraph::class),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => function ($root, $args, AppContext $app) {
                    $args['filters'][] = ['column' => 'ClosingRequest.AssignedCustomer_id', 'value' => $root['id']];
                    return (new ClosingRequestController())->viewList($app->user, new GraphqlInputRequest($args));
                }
            ],
            'recycleRequests' => [
                'type' => TypeRegistry::paginationType(RecycleRequestInSalesBCGraph::class),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => function ($root, $args, AppContext $app) {
                    $args['filters'][] = ['column' => 'RecycleRequest.AssignedCustomer_id', 'value' => $root['id']];
                    return (new RecycleRequestController())->viewList($app->user, new GraphqlInputRequest($args));
                }
            ],
        ];
    }

    protected function getClassMetadata(): string
    {
        return AssignedCustomer::class;
    }
}
