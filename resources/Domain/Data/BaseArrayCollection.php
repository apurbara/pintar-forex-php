<?php

namespace Resources\Domain\Data;

use Countable;
use Iterator;

abstract class BaseArrayCollection implements Countable, Iterator
{

    protected $collection = [];

    public function count(): int
    {
        return count($this->collection);
    }

    public function current(): mixed
    {
        return current($this->collection);
    }

    public function key(): mixed
    {
        return key($this->collection);
    }

    public function next(): void
    {
        next($this->collection);
    }

    public function rewind(): void
    {
        reset($this->collection);
    }

    public function valid(): bool
    {
        return key($this->collection) !== null;
    }

    function __construct()
    {
        $this->collection = $collection = [];
    }

}
