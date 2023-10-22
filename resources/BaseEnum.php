<?php

namespace Resources;

use ReflectionClass;
use Resources\Exception\RegularException;

abstract class BaseEnum
{

    protected $value;

    public function __construct($value)
    {
        $c = new ReflectionClass($this);
        if (!in_array($value, $c->getConstants())) {
            $path = explode('\\', static::class);
            $className = array_pop($path);
            throw RegularException::badRequest("bad request: invalid {$className} argument");
        }
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
    
    public function getDisplayValue(): string
    {
        $c = new ReflectionClass($this);
        return array_search($this->value, $c->getConstants());
    }
    
    public static function getValueDefinition(): array
    {
        $c = new ReflectionClass(static::class);
        return $c->getConstants();
    }

}
