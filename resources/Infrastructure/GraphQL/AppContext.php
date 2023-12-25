<?php

namespace Resources\Infrastructure\GraphQL;

class AppContext
{

    protected $parameters = [];

    public function __construct()
    {
        
    }

    public function storeParameter(string $key, mixed $parameter): void
    {
        $this->parameters[$key] = $parameter;
    }

    public function getParameter(string $key): mixed
    {
        return $this->parameters[$key] ?? null;
    }
}
