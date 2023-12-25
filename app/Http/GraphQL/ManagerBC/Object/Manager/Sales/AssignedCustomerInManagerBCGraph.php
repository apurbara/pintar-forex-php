<?php

namespace App\Http\GraphQL\ManagerBC\Object\Manager\Sales;

use App\Http\GraphQL\ManagerBC\Object\Manager\SalesInManagerBCGraph;
use Company\Domain\Model\Personnel\Manager\Sales;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;

class AssignedCustomerInManagerBCGraph extends GraphqlObjectType
{

    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
            'sales' => [
                'type' => TypeRegistry::objectType(SalesInManagerBCGraph::class),
                'resolve' => fn($root) => $this->buildDoctrineRepository(Sales::class)->fetchOneById($root['Sales_id']),
            ],
            'customer' => [
                'type' => TypeRegistry::objectType(Customer::class),
                'resolve' => fn($root) => $this->buildDoctrineRepository(Customer::class)->fetchOneById($root['Customer_id']),
            ],
            'customerJourney' => [
                'type' => TypeRegistry::objectType(CustomerJourneyGraph::class),
                'resolve' => fn ($root) => isset($root['CustomerJourney_id']) ? $this->buildDoctrineRepository(\Company\Domain\Model\CustomerJourney::class)->fetchOneById($root['CustomerJourney_id']) : null
            ],
            'schedules' => [
                'type' => TypeRegistry::paginationType(SalesActivityScheduleInSalesBCGraph::class),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => function ($root, $args, AppContext $app) {
                    $args['filters'][] = ['column' => 'SalesActivitySchedule.AssignedCustomer_id', 'value' => $root['id']];
                    return app(\Doctrine\ORM\EntityManager::class)->getRepository(\Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\SalesActivitySchedule::class);
                }
            ],
//            'closingRequests' => [
//                'type' => TypeRegistry::paginationType(ClosingRequestInSalesBCGraph::class),
//                'args' => InputListSchema::paginationListSchema(),
//                'resolve' => function ($root, $args, AppContext $app) {
//                    $args['filters'][] = ['column' => 'ClosingRequest.AssignedCustomer_id', 'value' => $root['id']];
//                    return (new ClosingRequestController())->viewList($app->user, new GraphqlInputRequest($args));
//                }
//            ],
//            'recycleRequests' => [
//                'type' => TypeRegistry::paginationType(RecycleRequestInSalesBCGraph::class),
//                'args' => InputListSchema::paginationListSchema(),
//                'resolve' => function ($root, $args, AppContext $app) {
//                    $args['filters'][] = ['column' => 'RecycleRequest.AssignedCustomer_id', 'value' => $root['id']];
//                    return (new RecycleRequestController())->viewList($app->user, new GraphqlInputRequest($args));
//                }
//            ],
        ];
    }

    protected function getClassMetadata(): string
    {
        return AssignedCustomer::class;
    }
}
