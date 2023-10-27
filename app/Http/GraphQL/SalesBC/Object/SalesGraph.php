<?php

namespace App\Http\GraphQL\SalesBC\Object;

use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use Sales\Domain\Model\Personnel\Sales;

class SalesGraph extends GraphqlObjectType
{

    protected function getClassMetadata(): string
    {
        return Sales::class;
    }
}
