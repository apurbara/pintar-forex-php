<?php

namespace App\Http\GraphQL\CompanyBC\Object;

use Company\Domain\Model\AreaStructure\Area\Customer;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;

class CustomerObjectInCompanyBC extends GraphqlObjectType
{

    protected function getClassMetadata(): string
    {
        return Customer::class;
    }

    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
            'verificationScore' => Type::int(),
        ];
    }
}
