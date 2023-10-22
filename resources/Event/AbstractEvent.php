<?php

namespace Resources\Event;

use ReflectionClass;

abstract readonly class AbstractEvent implements EventInterface
{

    public function getName(): string
    {
        return (new ReflectionClass($this))->getShortName();
    }

    public static function eventName(): string
    {
        return (new \ReflectionClass(static::class))->getShortName();
    }
}
