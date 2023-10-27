<?php

namespace App\Http\GraphQL\UserBC\Object;

use App\Http\Controllers\UserRole\UserRoleBuilder;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use User\Domain\Model\Admin;

class AdminLoginResponseGraph extends GraphqlObjectType
{
    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
            'token' => [
                'type' => Type::string(),
                'resolve' => fn($root) => UserRoleBuilder::generateJwtToken(UserRoleBuilder::ADMIN, $root['id']),
            ],
        ];
    }

    protected function getClassMetadata(): string
    {
        return Admin::class;
    }
}
