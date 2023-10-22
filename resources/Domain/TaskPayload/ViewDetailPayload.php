<?php

namespace Resources\Domain\TaskPayload;

readonly class ViewDetailPayload
{

    public array $result;

    public function __construct(public string $id)
    {
        
    }

    public function setResult(array $result)
    {
        $this->result = $result;
    }
}
