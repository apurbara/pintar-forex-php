<?php

namespace Resources\Infrastructure\GraphQL;

use GraphQL\Type\Definition\EnumType;
use ReflectionClass;

abstract class GraphqlEnumType extends EnumType
{

    public function __construct()
    {
        parent::__construct([
            'name' => (new ReflectionClass($this))->getShortName(),
            'values' => $this->valueDefinition(),
        ]);
    }

    /**
     * expected format:
     * [
     *      'DISPLAY_VALUE' => 'value'
     * ]
     */
    abstract protected function valueDefinition(): array;
}
