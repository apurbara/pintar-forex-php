<?php

namespace Resources\Infrastructure\GraphQL\ViewPaginationList;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class CursorLimit extends ObjectType
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
            'pageSize' => Type::int(),
            'total' => Type::int(),
            'cursorToPreviousPage' => Type::string(),
            'cursorToNextPage' => Type::string(),
        ];
    }
}
