<?php

namespace App\Http\GraphQL\CompanyBC\Object;

use App\Http\Controllers\CompanyBC\InCompany\Personnel\Manager\SalesController;
use App\Http\Controllers\CompanyBC\InCompany\Personnel\ManagerController;
use App\Http\GraphQL\AppContext;
use App\Http\GraphQL\CompanyBC\Object\Personnel\ManagerGraph;
use App\Http\GraphQL\GraphqlInputRequest;
use Company\Domain\Model\Personnel;
use Company\Domain\Model\Personnel\Manager\Sales;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use Resources\Infrastructure\GraphQL\InputListSchema;
use Resources\Infrastructure\GraphQL\Pagination;
use Resources\Infrastructure\GraphQL\TypeRegistry;

class PersonnelGraph extends GraphqlObjectType
{

    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
            'managerAssignments' => [
                'type' => new Pagination(TypeRegistry::objectType(ManagerGraph::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => function ($root, $args, AppContext $app) {
                    $args['filters'][] = ['column' => 'Manager.Personnel_id', 'value' => $root['id']];
                    return (new ManagerController())->viewList($app->user, new GraphqlInputRequest($args));
                }
            ],
            'salesAssignments' => [
                'type' => new Pagination(TypeRegistry::objectType(Sales::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => function ($root, $args, AppContext $app) {
                    $args['filters'][] = ['column' => 'Sales.Personnel_id', 'value' => $root['id']];
                    return (new SalesController())->viewList($app->user, new GraphqlInputRequest($args));
                }
            ],
        ];
    }

    protected function getClassMetadata(): string
    {
        return Personnel::class;
    }
}
