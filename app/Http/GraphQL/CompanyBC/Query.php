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
use App\Http\GraphQL\GraphqlInputRequest;
use Company\Domain\Model\AreaStructure;
use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\CustomerVerification;
use Company\Domain\Model\Personnel;
use Company\Domain\Model\Personnel\Manager;
use Company\Domain\Model\Personnel\Manager\Sales;
use Company\Domain\Model\SalesActivity;
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
                'type' => new Pagination(TypeRegistry::objectType(Personnel::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new PersonnelController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'personnelDetail' => [
                'type' => TypeRegistry::objectType(Personnel::class),
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
                'type' => new Pagination(TypeRegistry::objectType(AreaStructure::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new AreaStructureController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'areaStructureDetail' => [
                'type' => TypeRegistry::objectType(AreaStructure::class),
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
                'type' => new Pagination(TypeRegistry::objectType(Area::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new AreaController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'areaDetail' => [
                'type' => TypeRegistry::objectType(Area::class),
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
                'type' => new Pagination(TypeRegistry::objectType(Manager::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new ManagerController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'managerDetail' => [
                'type' => TypeRegistry::objectType(Manager::class),
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
                'type' => new Pagination(TypeRegistry::objectType(Sales::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new SalesController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'salesDetail' => [
                'type' => TypeRegistry::objectType(Sales::class),
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
                'type' => new Pagination(TypeRegistry::objectType(CustomerVerification::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new CustomerVerificationController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'customerVerificationDetail' => [
                'type' => TypeRegistry::objectType(CustomerVerification::class),
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
                'type' => new Pagination(TypeRegistry::objectType(SalesActivity::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new SalesActivityController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'salesActivityDetail' => [
                'type' => TypeRegistry::objectType(SalesActivity::class),
                'args' => ['salesActivityId' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new SalesActivityController())
                        ->viewDetail($app->user, $args['salesActivityId'])
            ],
        ];
    }
}
