<?php

namespace Admin\Application\GraphQL\Object;

use Admin\Domain\Model\Admin;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;

class AdminLoginResponse extends GraphqlObjectType
{
    
    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
            'token' => Type::string(),
        ];
    }
    
    protected function getClassMetadata(): string
    {
        return Admin::class;
    }
}
