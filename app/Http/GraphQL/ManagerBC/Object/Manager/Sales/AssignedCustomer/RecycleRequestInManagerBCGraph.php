<?php

namespace App\Http\GraphQL\ManagerBC\Object\Manager\Sales\AssignedCustomer;

use App\Http\GraphQL\ManagerBC\Object\Manager\Sales\AssignedCustomerInManagerBCGraph;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\RecycleRequest;

class RecycleRequestInManagerBCGraph extends GraphqlObjectType
{

    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
            'assignedCustomer' => [
                'type' => TypeRegistry::objectType(AssignedCustomerInManagerBCGraph::class),
                'resolve' => fn($root) => $this->buildDoctrineRepository(AssignedCustomer::class)
                        ->fetchOneById($root['AssignedCustomer_id'])
            ],
        ];
    }

    protected function getClassMetadata(): string
    {
        return RecycleRequest::class;
    }
}
