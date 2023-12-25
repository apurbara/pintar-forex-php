<?php

namespace Resources\Infrastructure\GraphQL;

use Resources\Application\InputRequest;

class GraphqlInputRequest implements InputRequest
{

    public function __construct(protected array $args)
    {
        
    }

    public function get(string $key): mixed
    {
        return $this->args[$key] ?? null;
    }
}
