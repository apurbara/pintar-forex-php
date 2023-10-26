<?php

namespace App\Http\GraphQL\SalesBC;

use App\Http\Controllers\SalesBC\AssignedCustomerController;
use App\Http\Controllers\SalesBC\ScheduledSalesActivityController;
use App\Http\GraphQL\AppContext;
use App\Http\GraphQL\GraphqlInputRequest;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\InputListSchema;
use Resources\Infrastructure\GraphQL\Pagination;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\ScheduledSalesActivity;

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
        $type = new ObjectType([
            'name' => 'salesQuery', 
            'fields' => fn() => [
                ...$this->assignedCustomerQuery(),
                ...$this->scheduledSalesActivityQuery(),
            ],
        ]);
        return [
            'sales' => [
                'type' => $type,
                'args' => ['salesId' => Type::nonNull(Type::id())],
                'resolve' => function($root, $args, AppContext $app) use($type) {
                    $app->user = $app->user->authorizedAsSales($args['salesId']);
                    return $type;
                }
            ]
        ];
    }

    protected function assignedCustomerQuery(): array
    {
        return [
            'assignedCustomerList' => [
                'type' => new Pagination(TypeRegistry::objectType(AssignedCustomer::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new AssignedCustomerController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'assignedCustomerDetail' => [
                'type' => TypeRegistry::objectType(AssignedCustomer::class),
                'args' => ['assignedCustomerId' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new AssignedCustomerController())
                        ->viewDetail($app->user, $args['assignedCustomerId'])
            ],
        ];
    }

    protected function scheduledSalesActivityQuery(): array
    {
        return [
            'scheduledSalesActivityList' => [
                'type' => new Pagination(TypeRegistry::objectType(ScheduledSalesActivity::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new ScheduledSalesActivityController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'scheduledSalesActivityDetail' => [
                'type' => TypeRegistry::objectType(ScheduledSalesActivity::class),
                'args' => ['scheduledSalesActivityId' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new ScheduledSalesActivityController())
                        ->viewDetail($app->user, $args['scheduledSalesActivityId'])
            ],
        ];
    }
}
