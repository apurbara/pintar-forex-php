<?php

namespace App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomer;

use App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomerInSalesBCGraph;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\ClosingRequest;

class ClosingRequestInSalesBCGraph extends GraphqlObjectType
{

    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
            'assignedCustomer' => [
                'type' => TypeRegistry::objectType(AssignedCustomerInSalesBCGraph::class),
                'resolve' => fn($root) => $this->buildDoctrineRepository(AssignedCustomer::class)->fetchOneById($root['AssignedCustomer_id'])
            ],
        ];
    }

    protected function getClassMetadata(): string
    {
        return ClosingRequest::class;
    }
}
