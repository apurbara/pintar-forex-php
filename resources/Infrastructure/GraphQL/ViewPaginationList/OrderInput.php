<?php

namespace Resources\Infrastructure\GraphQL\ViewPaginationList;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

class OrderInput extends InputObjectType
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
            'column' => Type::nonNull(Type::string()),
            'direction' => Type::string(),
        ];
    }
}
