<?php

namespace App\Http\GraphQL\SalesBC;

use App\Http\Controllers\SalesBC\AssignedCustomerController;
use App\Http\Controllers\SalesBC\ClosingRequestController;
use App\Http\Controllers\SalesBC\RecycleRequestController;
use App\Http\Controllers\SalesBC\SalesActivityReportController;
use App\Http\Controllers\SalesBC\SalesActivityScheduleController;
use App\Http\Controllers\SalesBC\VerificationReportController;
use App\Http\GraphQL\AppContext;
use App\Http\GraphQL\GraphqlInputRequest;
use App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomer\ClosingRequestInSalesBCGraph;
use App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomer\Customer\VerificationReportInSalesBCGraph;
use App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomer\RecycleRequestInSalesBCGraph;
use App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomer\SalesActivityScheduleInSalesBCGraph;
use App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomerInSalesBCGraph;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\InputListSchema;
use Resources\Infrastructure\GraphQL\Pagination;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Resources\Infrastructure\GraphQL\ViewList\FilterInput;
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
        ...$this->verificationReportQuery(),
        ...$this->closingRequestQuery(),
        ...$this->recycleRequestQuery(),
            ],
        ]);
        return [
            'sales' => [
                'type' => $type,
                'args' => ['salesId' => Type::nonNull(Type::id())],
                'resolve' => function ($root, $args, AppContext $app) use ($type) {
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
                'type' => new Pagination(TypeRegistry::objectType(AssignedCustomerInSalesBCGraph::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new AssignedCustomerController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'assignedCustomerDetail' => [
                'type' => TypeRegistry::objectType(AssignedCustomerInSalesBCGraph::class),
                'args' => ['assignedCustomerId' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new AssignedCustomerController())
                        ->viewDetail($app->user, $args['assignedCustomerId'])
            ],
            'totalCustomerAssignment' => [
                'type' => Type::int(),
                'args' => ['filters' => Type::listOf(TypeRegistry::inputType(FilterInput::class)),],
                'resolve' => fn($root, $args, AppContext $app) => (new AssignedCustomerController())
                        ->viewTotalCustomerAssignment($app->user, new GraphqlInputRequest($args))
            ],
        ];
    }

    protected function salesActivityScheduleQuery(): array
    {
        return [
            'salesActivityScheduleList' => [
                'type' => new Pagination(TypeRegistry::objectType(SalesActivityScheduleInSalesBCGraph::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new SalesActivityScheduleController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'salesActivityScheduleDetail' => [
                'type' => TypeRegistry::objectType(SalesActivityScheduleInSalesBCGraph::class),
                'args' => ['salesActivityScheduleId' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new SalesActivityScheduleController())
                        ->viewDetail($app->user, $args['salesActivityScheduleId'])
            ],
            'totalSalesActivitySchedule' => [
                'type' => Type::int(),
                'args' => ['filters' => Type::listOf(TypeRegistry::inputType(FilterInput::class)),],
                'resolve' => fn($root, $args, AppContext $app) => (new SalesActivityScheduleController())
                        ->viewTotalSchedule($app->user, new GraphqlInputRequest($args))
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

    protected function verificationReportQuery(): array
    {
        return [
            'verificationReportList' => [
                'type' => new Pagination(TypeRegistry::objectType(VerificationReportInSalesBCGraph::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new VerificationReportController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'verificationReportDetail' => [
                'type' => TypeRegistry::objectType(VerificationReportInSalesBCGraph::class),
                'args' => ['verificationReportId' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new VerificationReportController())
                        ->viewDetail($app->user, $args['verificationReportId'])
            ],
        ];
    }

    protected function closingRequestQuery(): array
    {
        return [
            'closingRequestList' => [
                'type' => new Pagination(TypeRegistry::objectType(ClosingRequestInSalesBCGraph::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new ClosingRequestController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'closingRequestDetail' => [
                'type' => TypeRegistry::objectType(ClosingRequestInSalesBCGraph::class),
                'args' => ['closingRequestId' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new ClosingRequestController())
                        ->viewDetail($app->user, $args['closingRequestId'])
            ],
        ];
    }

    protected function recycleRequestQuery(): array
    {
        return [
            'recycleRequestList' => [
                'type' => new Pagination(TypeRegistry::objectType(RecycleRequestInSalesBCGraph::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new RecycleRequestController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'recycleRequestDetail' => [
                'type' => TypeRegistry::objectType(RecycleRequestInSalesBCGraph::class),
                'args' => ['recycleRequestId' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new RecycleRequestController())
                        ->viewDetail($app->user, $args['recycleRequestId'])
            ],
        ];
    }
}
