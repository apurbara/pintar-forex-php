<?php

namespace App\Http\GraphQL;

use App\Http\Controllers\InputRequest;

class GraphqlInputRequest implements InputRequest
{

    protected readonly array $args;

    public function __construct(array $args)
    {
        $this->args = $args;
    }

    public function get(string $key)
    {
        return $this->args[$key] ?? null;
    }
}
