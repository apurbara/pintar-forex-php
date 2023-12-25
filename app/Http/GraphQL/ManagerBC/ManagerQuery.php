<?php

namespace App\Http\GraphQL\ManagerBC;

use App\Http\Controllers\ManagerBC\AssignedCustomerController;
use App\Http\Controllers\ManagerBC\ClosingRequestController;
use App\Http\Controllers\ManagerBC\RecycleRequestController;
use App\Http\GraphQL\ManagerBC\Object\Manager\Sales\AssignedCustomer\ClosingRequestInManagerBCGraph;
use App\Http\GraphQL\ManagerBC\Object\Manager\Sales\AssignedCustomer\RecycleRequestInManagerBCGraph;
use App\Http\GraphQL\ManagerBC\Object\Manager\Sales\AssignedCustomerInManagerBCGraph;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\AppContext;
use Resources\Infrastructure\GraphQL\GraphqlInputRequest;
use Resources\Infrastructure\GraphQL\InputListSchema;
use Resources\Infrastructure\GraphQL\TypeRegistry;

class ManagerQuery extends ObjectType
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
            ...$this->closingRequestQuery(),
            ...$this->recycleRequestQuery(),
            ...$this->assignedCustomerQuery(),
        ];
    }
    //
    protected function closingRequestQuery(): array
    {
        return [
            'closingRequestList' => [
                'type' => TypeRegistry::paginationType(ClosingRequestInManagerBCGraph::class),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new ClosingRequestController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'closingRequestDetail' => [
                'type' => TypeRegistry::objectType(ClosingRequestInManagerBCGraph::class),
                'args' => ['id' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new ClosingRequestController())
                        ->viewDetail($app->user, $args['id'])
            ],
        ];
    }
    //
    protected function recycleRequestQuery(): array
    {
        return [
            'recycleRequestList' => [
                'type' => TypeRegistry::paginationType(RecycleRequestInManagerBCGraph::class),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new RecycleRequestController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'recycleRequestDetail' => [
                'type' => TypeRegistry::objectType(RecycleRequestInManagerBCGraph::class),
                'args' => ['id' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new RecycleRequestController())
                        ->viewDetail($app->user, $args['id'])
            ],
        ];
    }
    //
    protected function assignedCustomerQuery(): array
    {
        return [
            'assignedCustomerList' => [
                'type' => TypeRegistry::paginationType(AssignedCustomerInManagerBCGraph::class),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new AssignedCustomerController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'assignedCustomerDetail' => [
                'type' => TypeRegistry::objectType(AssignedCustomerInManagerBCGraph::class),
                'args' => ['id' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new AssignedCustomerController())
                        ->viewDetail($app->user, $args['id'])
            ],
        ];
    }
}
