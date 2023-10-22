<?php

namespace Resources\Infrastructure\GraphQL\CustomTypes;

use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;

class AnyType extends ScalarType
{

    public function parseLiteral(Node $valueNode, ?array $variables = null)
    {
        return $valueNode->value;
    }

    public function parseValue($value)
    {
        return $value;
    }

    public function serialize($value)
    {
        return $this->parseValue($value);
    }

}
