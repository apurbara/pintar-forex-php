<?php

namespace Resources\Domain\TaskPayload;

readonly class ViewPayload
{

    public array $result;

    public function setResult(array $result)
    {
        $this->result = $result;
    }
}
