<?php

namespace Resources\Infrastructure\GraphQL\ViewPaginationList;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class OffsetLimit extends ObjectType
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
            'page' => Type::int(),
            'pageSize' => Type::int(),
        ];
    }
}
