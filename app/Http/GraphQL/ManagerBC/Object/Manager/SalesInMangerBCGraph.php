<?php

namespace App\Http\GraphQL\ManagerBC\Object\Manager;

use App\Http\GraphQL\CompanyBC\Object\PersonnelGraph;
use Company\Domain\Model\Personnel;
use Company\Domain\Model\Personnel\Manager\Sales;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use Resources\Infrastructure\GraphQL\TypeRegistry;

class SalesInMangerBCGraph extends GraphqlObjectType
{

    protected function fieldDefinition(): array
    {
        return [
            parent::fieldDefinition(),
            'personnel' => [
                'type' => TypeRegistry::objectType(PersonnelGraph::class),
                'resolve' => fn($root) => $this->buildDoctrineRepository(Personnel::class)->fetchOneById($root['Personnel_id'])
            ],
        ];
    }

    protected function getClassMetadata(): string
    {
        return Sales::class;
    }
}
