<?php

namespace App\Http\GraphQL\CompanyBC\Object\Personnel\Manager;

use App\Http\Controllers\CompanyBC\InCompany\AreaStructure\AreaController;
use App\Http\Controllers\CompanyBC\InCompany\Personnel\ManagerController;
use App\Http\Controllers\CompanyBC\InCompany\PersonnelController;
use App\Http\GraphQL\AppContext;
use App\Http\GraphQL\CompanyBC\Object\AreaStructure\AreaGraph;
use App\Http\GraphQL\CompanyBC\Object\Personnel\ManagerGraph;
use App\Http\GraphQL\CompanyBC\Object\PersonnelGraph;
use Company\Domain\Model\Personnel\Manager\Sales;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use Resources\Infrastructure\GraphQL\TypeRegistry;

class SalesGraph extends GraphqlObjectType
{

    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
            'area' => [
                'type' => TypeRegistry::objectType(AreaGraph::class),
                'resolve' => fn($root, $args, AppContext $app) =>
                (new AreaController())->viewDetail($app->user, $root['Area_id'])
            ],
            'personnel' => [
                'type' => TypeRegistry::objectType(PersonnelGraph::class),
                'resolve' => fn($root, $args, AppContext $app) =>
                (new PersonnelController())->viewDetail($app->user, $root['Personnel_id'])
            ],
            'manager' => [
                'type' => TypeRegistry::objectType(ManagerGraph::class),
                'resolve' => fn($root, $args, AppContext $app) =>
                (new ManagerController())->viewDetail($app->user, $root['Manager_id'])
            ],
        ];
    }

    protected function getClassMetadata(): string
    {
        return Sales::class;
    }
}
