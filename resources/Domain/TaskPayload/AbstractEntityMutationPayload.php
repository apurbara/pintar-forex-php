<?php

namespace Resources\Domain\TaskPayload;

abstract readonly class AbstractEntityMutationPayload
{

    public string $id;

    public function setId(string $id): static
    {
        $this->id = $id;
        return $this;
    }
}
