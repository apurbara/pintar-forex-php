<?php

namespace Resources\Infrastructure\Rest;

use Illuminate\Http\Request;
use Resources\Application\InputRequest;

class RestInputRequest implements InputRequest
{

    public function __construct(protected Request $request)
    {
        
    }

    public function get(string $key): mixed
    {
        return $this->request->input($key) ?? $this->request->query($key);
    }
}
