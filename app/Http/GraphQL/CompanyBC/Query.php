<?php

namespace App\Http\GraphQL\CompanyBC;

use App\Http\Controllers\CompanyBC\InCompany\AreaStructure\AreaController;
use App\Http\Controllers\CompanyBC\InCompany\AreaStructureController;
use App\Http\Controllers\CompanyBC\InCompany\CustomerVerificationController;
use App\Http\Controllers\CompanyBC\InCompany\Personnel\Manager\SalesController;
use App\Http\Controllers\CompanyBC\InCompany\Personnel\ManagerController;
use App\Http\Controllers\CompanyBC\InCompany\PersonnelController;
use App\Http\Controllers\CompanyBC\InCompany\SalesActivityController;
use App\Http\GraphQL\AppContext;
use App\Http\GraphQL\CompanyBC\Object\AreaStructure\AreaGraph;
use App\Http\GraphQL\CompanyBC\Object\AreaStructureGraph;
use App\Http\GraphQL\CompanyBC\Object\CustomerVerificationGraph;
use App\Http\GraphQL\CompanyBC\Object\Personnel\ManagerGraph;
use App\Http\GraphQL\CompanyBC\Object\PersonnelGraph;
use App\Http\GraphQL\CompanyBC\Object\SalesActivityGraph;
use App\Http\GraphQL\GraphqlInputRequest;
use App\Http\GraphQL\SalesBC\Object\SalesGraph;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\InputListSchema;
use Resources\Infrastructure\GraphQL\Pagination;
use Resources\Infrastructure\GraphQL\TypeRegistry;

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
            ...$this->personnelQuery(),
            ...$this->areaStructureQuery(),
            ...$this->areaQuery(),
            ...$this->managerQuery(),
            ...$this->salesQuery(),
            ...$this->customerVerificationQuery(),
            ...$this->salesActivityQuery(),
        ];
    }

    protected function personnelQuery(): array
    {
        return [
            'personnelList' => [
                'type' => new Pagination(TypeRegistry::objectType(PersonnelGraph::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new PersonnelController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'personnelDetail' => [
                'type' => TypeRegistry::objectType(PersonnelGraph::class),
                'args' => ['personnelId' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new PersonnelController())
                        ->viewDetail($app->user, $args['personnelId'])
            ],
        ];
    }

    protected function areaStructureQuery(): array
    {
        return [
            'areaStructureList' => [
                'type' => new Pagination(TypeRegistry::objectType(AreaStructureGraph::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new AreaStructureController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'areaStructureDetail' => [
                'type' => TypeRegistry::objectType(AreaStructureGraph::class),
                'args' => ['areaStructureId' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new AreaStructureController())
                        ->viewDetail($app->user, $args['areaStructureId'])
            ],
        ];
    }

    protected function areaQuery(): array
    {
        return [
            'areaList' => [
                'type' => new Pagination(TypeRegistry::objectType(AreaGraph::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new AreaController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'areaDetail' => [
                'type' => TypeRegistry::objectType(AreaGraph::class),
                'args' => ['areaId' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new AreaController())
                        ->viewDetail($app->user, $args['areaId'])
            ],
        ];
    }

    protected function managerQuery(): array
    {
        return [
            'managerList' => [
                'type' => new Pagination(TypeRegistry::objectType(ManagerGraph::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new ManagerController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'managerDetail' => [
                'type' => TypeRegistry::objectType(ManagerGraph::class),
                'args' => ['managerId' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new ManagerController())
                        ->viewDetail($app->user, $args['managerId'])
            ],
        ];
    }

    protected function salesQuery(): array
    {
        return [
            'salesList' => [
                'type' => new Pagination(TypeRegistry::objectType(SalesGraph::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new SalesController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'salesDetail' => [
                'type' => TypeRegistry::objectType(SalesGraph::class),
                'args' => ['salesId' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new SalesController())
                        ->viewDetail($app->user, $args['salesId'])
            ],
        ];
    }

    protected function customerVerificationQuery(): array
    {
        return [
            'customerVerificationList' => [
                'type' => new Pagination(TypeRegistry::objectType(CustomerVerificationGraph::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new CustomerVerificationController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'customerVerificationDetail' => [
                'type' => TypeRegistry::objectType(CustomerVerificationGraph::class),
                'args' => ['customerVerificationId' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new CustomerVerificationController())
                        ->viewDetail($app->user, $args['customerVerificationId'])
            ],
        ];
    }

    protected function salesActivityQuery(): array
    {
        return [
            'salesActivityList' => [
                'type' => new Pagination(TypeRegistry::objectType(SalesActivityGraph::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new SalesActivityController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'salesActivityDetail' => [
                'type' => TypeRegistry::objectType(SalesActivityGraph::class),
                'args' => ['salesActivityId' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new SalesActivityController())
                        ->viewDetail($app->user, $args['salesActivityId'])
            ],
        ];
    }
}
