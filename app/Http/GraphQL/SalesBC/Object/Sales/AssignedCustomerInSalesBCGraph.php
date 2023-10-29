<?php

namespace App\Http\GraphQL\SalesBC\Object\Sales;

use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;

class AssignedCustomerInSalesBCGraph extends GraphqlObjectType
{
    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
        ];
    }
    
    protected function getClassMetadata(): string
    {
        return AssignedCustomer::class;
    }
}
