<?php

namespace Resources\Domain\Data;

class DataCollection extends BaseArrayCollection 
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function push($value, ?string $key = null): void
    {
        if (empty($key)) {
            $this->collection[] = $value;
        } else {
            $this->collection[$key] = $value;
        }
    }
    
    public function pull(string $key)
    {
        if (!isset($this->collection[$key])) {
            return null;
        }
        $value = $this->collection[$key];
        unset($this->collection[$key]);
        return $value;
    }

    protected function getCollection(): array
    {
        return $this->collection;
    }

}
