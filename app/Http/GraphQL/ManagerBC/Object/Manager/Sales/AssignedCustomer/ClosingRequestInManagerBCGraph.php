<?php

namespace App\Http\GraphQL\ManagerBC\Object\Manager\Sales\AssignedCustomer;

use App\Http\GraphQL\ManagerBC\Object\Manager\Sales\AssignedCustomerInManagerBCGraph;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\ClosingRequest;

class ClosingRequestInManagerBCGraph extends GraphqlObjectType
{
    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
            'assignedCustomer' => [
                'type' => TypeRegistry::objectType(AssignedCustomerInManagerBCGraph::class),
                'resolve' => fn($root) => $this->buildDoctrineRepository(AssignedCus)
            ],
        ];
    }
    
    protected function getClassMetadata(): string
    {
        return ClosingRequest::class;
    }
}
