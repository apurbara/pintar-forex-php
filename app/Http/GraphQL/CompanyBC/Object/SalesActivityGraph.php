<?php

namespace App\Http\GraphQL\CompanyBC\Object;

use Company\Domain\Model\SalesActivity;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;

class SalesActivityGraph extends GraphqlObjectType
{

    protected function getClassMetadata(): string
    {
        return SalesActivity::class;
    }
}
