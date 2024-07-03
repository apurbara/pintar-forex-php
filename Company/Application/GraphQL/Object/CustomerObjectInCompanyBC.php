<?php

namespace Company\Application\GraphQL\Object;

use Company\Domain\Model\Customer;
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
