<?php

namespace Resources\Infrastructure\GraphQL\CustomTypes;

use DateTimeZone;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;
use Resources\Exception\RegularException;

class DateTimeZ extends ScalarType
{
    
    public function parseLiteral(Node $valueNode, $variables = null): mixed
    {
        return (new \DateTime($valueNode->value))
                ->setTimezone(new DateTimeZone('Asia/Jakarta'))
                ->format('Y-m-d\TH:i:sP');
    }
    
    public function parseValue($value): mixed
    {
        return (new \DateTime($value))
                ->setTimezone(new DateTimeZone('Asia/Jakarta'))
                ->format('Y-m-d\TH:i:sP');
    }

    public function serialize($value): mixed
    {
        return $this->parseValue($value);
    }
}
