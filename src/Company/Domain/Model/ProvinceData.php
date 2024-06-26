<?php

namespace Company\Domain\Model;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class ProvinceData extends AbstractEntityMutationPayload
{

    public ?string $name;

    public function setName(?string $name)
    {
        $this->name = $name;
        return $this;
    }
}
