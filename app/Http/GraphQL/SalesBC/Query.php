<?php

namespace App\Http\GraphQL\SalesBC;

use App\Http\Controllers\SalesBC\AssignedCustomerController;
use App\Http\Controllers\SalesBC\SalesActivityReportController;
use App\Http\Controllers\SalesBC\SalesActivityScheduleController;
use App\Http\GraphQL\AppContext;
use App\Http\GraphQL\GraphqlInputRequest;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\InputListSchema;
use Resources\Infrastructure\GraphQL\Pagination;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule\SalesActivityReport;

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
                ...$this->salesActivityScheduleQuery(),
                ...$this->salesActivityReportQuery(),
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

    protected function salesActivityScheduleQuery(): array
    {
        return [
            'salesActivityScheduleList' => [
                'type' => new Pagination(TypeRegistry::objectType(SalesActivitySchedule::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new SalesActivityScheduleController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'salesActivityScheduleDetail' => [
                'type' => TypeRegistry::objectType(SalesActivitySchedule::class),
                'args' => ['salesActivityScheduleId' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new SalesActivityScheduleController())
                        ->viewDetail($app->user, $args['salesActivityScheduleId'])
            ],
        ];
    }

    protected function salesActivityReportQuery(): array
    {
        return [
            'salesActivityReportList' => [
                'type' => new Pagination(TypeRegistry::objectType(SalesActivityReport::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new SalesActivityReportController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'salesActivityReportDetail' => [
                'type' => TypeRegistry::objectType(SalesActivityReport::class),
                'args' => ['salesActivityReportId' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new SalesActivityReportController())
                        ->viewDetail($app->user, $args['salesActivityReportId'])
            ],
        ];
    }
}
