<?php

namespace App\Http\GraphQL\CompanyBC\Object;

use Company\Domain\Model\CustomerVerification;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;

class CustomerVerificationGraph extends GraphqlObjectType
{

    protected function getClassMetadata(): string
    {
        return CustomerVerification::class;
    }
}
