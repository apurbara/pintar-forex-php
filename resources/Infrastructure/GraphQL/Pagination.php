<?php

namespace Resources\Infrastructure\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\ViewPaginationList\CursorLimit;
use Resources\Infrastructure\GraphQL\ViewPaginationList\OffsetLimit;

class Pagination extends ObjectType
{

    public function __construct(Type $type)
    {
        parent::__construct([
            'fields' => fn() => [
                'list' => Type::listOf($type),
                'cursorLimit' => TypeRegistry::type(CursorLimit::class),
                'offsetLimit' => TypeRegistry::type(OffsetLimit::class),
            ],
        ]);
    }
}
