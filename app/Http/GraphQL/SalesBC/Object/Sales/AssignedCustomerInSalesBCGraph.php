<?php

namespace App\Http\GraphQL\SalesBC\Object\Sales;

use App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomer\CustomerInSalesBCGraph;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;

class AssignedCustomerInSalesBCGraph extends GraphqlObjectType
{
    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
            'customer' => [
                'type' => TypeRegistry::objectType(CustomerInSalesBCGraph::class),
                'resolve' => fn($root) => $this->buildDoctrineRepository(Customer::class)->fetchOneById($root['Customer_id'])
            ],
        ];
    }
    
    protected function getClassMetadata(): string
    {
        return AssignedCustomer::class;
    }
}
