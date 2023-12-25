<?php

namespace App\Http\GraphQL\CompanyBC\Object\Personnel;

use App\Http\Controllers\CompanyBC\InCompany\Personnel\Manager\SalesController;
use App\Http\GraphQL\CompanyBC\Object\PersonnelGraph;
use Company\Domain\Model\Personnel;
use Company\Domain\Model\Personnel\Manager;
use Company\Domain\Model\Personnel\Manager\Sales;
use Resources\Infrastructure\GraphQL\AppContext;
use Resources\Infrastructure\GraphQL\GraphqlInputRequest;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use Resources\Infrastructure\GraphQL\InputListSchema;
use Resources\Infrastructure\GraphQL\TypeRegistry;

class ManagerGraph extends GraphqlObjectType
{

    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
            'personnel' => [
                'type' => TypeRegistry::objectType(PersonnelGraph::class),
                'resolve' => fn ($root) => $this->buildDoctrineRepository(Personnel::class)->fetchOneById($root['Personnel_id']),
            ],
            'managedSales' => [
                'type' => TypeRegistry::paginationType(Sales::class),
//                'type' => new Pagination(TypeRegistry::objectType(Sales::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => function ($root, $args, AppContext $app) {
                    $args['filters'][] = ['column' => 'Sales.Manager_id', 'value' => $root['id']];
                    return (new SalesController())
                        ->viewList($app->user, new GraphqlInputRequest($args));
                }
            ],
        ];
    }

    protected function getClassMetadata(): string
    {
        return Manager::class;
    }
}
