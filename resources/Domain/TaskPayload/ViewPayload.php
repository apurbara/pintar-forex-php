<?php

namespace Resources\Domain\TaskPayload;

readonly class ViewPayload
{

    public mixed $result;

    public function setResult(mixed $result)
    {
        $this->result = $result;
    }
}
