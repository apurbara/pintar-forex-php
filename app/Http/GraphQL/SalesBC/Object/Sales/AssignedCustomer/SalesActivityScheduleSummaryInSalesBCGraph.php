<?php

namespace App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomer;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\CustomTypes\DateTimeZ;
use Resources\Infrastructure\GraphQL\TypeRegistry;

class SalesActivityScheduleSummaryInSalesBCGraph extends ObjectType
{

    public function __construct()
    {
        parent::__construct([
            'fields' => fn() => $this->fieldDefinition(),
        ]);
    }

    protected function fieldDefinition(): array
    {
        return [
            'total' => Type::int(),
            'startTime' => TypeRegistry::type(DateTimeZ::class),
            'endTime' => TypeRegistry::type(DateTimeZ::class),
            'status' => Type::string(),
        ];
    }
}
