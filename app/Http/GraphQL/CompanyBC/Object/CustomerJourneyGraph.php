<?php

namespace App\Http\GraphQL\CompanyBC\Object;

use Company\Domain\Model\CustomerJourney;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;

class CustomerJourneyGraph extends GraphqlObjectType
{

    protected function getClassMetadata(): string
    {
        return CustomerJourney::class;
    }
}
