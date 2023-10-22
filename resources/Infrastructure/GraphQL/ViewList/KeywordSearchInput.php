<?php

namespace Resources\Infrastructure\GraphQL\ViewList;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\CustomTypes\AnyType;
use Resources\Infrastructure\GraphQL\TypeRegistry;

class KeywordSearchInput extends InputObjectType
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
            'comparisonType' => Type::string(),
            'columns' => Type::listOf(Type::string()),
            'value' => TypeRegistry::customType(AnyType::class),
        ];
    }
}
