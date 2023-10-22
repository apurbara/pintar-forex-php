<?php

namespace Resources\Infrastructure\GraphQL\CustomTypes;

use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;

class NoResponse extends ScalarType
{
    
    public function parseLiteral(Node $valueNode, ?array $variables = null)
    {
        return null;
    }

    public function parseValue($value)
    {
        return null;
    }

    public function serialize($value)
    {
        return null;
    }

}
