<?php

namespace App\Http\GraphQL\ManagerBC\Object\Manager\Sales;

use App\Http\GraphQL\ManagerBC\Object\Manager\SalesInMangerBCGraph;
use Company\Domain\Model\Personnel\Manager\Sales;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;

class AssignedCustomerInManagerBCGraph extends GraphqlObjectType
{

    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
            'sales' => [
                'type' => TypeRegistry::objectType(SalesInMangerBCGraph::class),
                'resolve' => fn($root) => $this->buildDoctrineRepository(Sales::class)->fetchOneById($root['Sales_id']),
            ],
            'customer' => [
                'type' => TypeRegistry::objectType(Customer::class),
                'resolve' => fn($root) => $this->buildDoctrineRepository(Customer::class)->fetchOneById($root['Customer_id']),
            ],
        ];
    }

    protected function getClassMetadata(): string
    {
        return AssignedCustomer::class;
    }
}
