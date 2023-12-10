<?php

namespace Resources\Infrastructure\GraphQL\CustomTypes;

use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;

class DateTimeZ extends ScalarType
{
    
    public function parseLiteral(Node $valueNode, $variables = null): mixed
    {
        return (new \DateTime($valueNode->value, new \DateTimeZone('Asia/Jakarta')))
                ->format('Y-m-d H:i:sP');
    }

    public function parseValue($value): mixed
    {
        return (new \DateTime($value, new \DateTimeZone('Asia/Jakarta')))
                ->format('Y-m-d\TH:i:sP');
    }

    public function serialize($value): mixed
    {
        return $this->parseValue($value);
    }
}
