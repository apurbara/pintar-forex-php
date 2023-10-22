<?php

namespace Resources\Infrastructure\GraphQL\ViewPaginationList;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\TypeRegistry;

class OffsetLimitInput extends InputObjectType
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
            'page' => Type::int(),
            'pageSize' => Type::int(),
            'orders' => Type::listOf(TypeRegistry::inputType(OrderInput::class)),
        ];
    }
}
