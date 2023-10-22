<?php

namespace Resources\Infrastructure\GraphQL;

use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\ViewList\FilterInput;
use Resources\Infrastructure\GraphQL\ViewList\KeywordSearchInput;
use Resources\Infrastructure\GraphQL\ViewPaginationList\CursorLimitInput;
use Resources\Infrastructure\GraphQL\ViewPaginationList\OffsetLimitInput;

class InputListSchema
{

    public static function paginationListSchema(): array
    {
        return [
            'keywordSearch' => TypeRegistry::inputType(KeywordSearchInput::class),
            'filters' => Type::listOf(TypeRegistry::inputType(FilterInput::class)),
            'cursorLimit' => TypeRegistry::inputType(CursorLimitInput::class),
            'offsetLimit' => TypeRegistry::inputType(OffsetLimitInput::class),
        ];
    }

    public static function allListSchema(): array
    {
        return [
            'keywordSearch' => TypeRegistry::inputType(KeywordSearchInput::class),
            'filters' => Type::listOf(TypeRegistry::inputType(FilterInput::class)),
        ];
    }
}
