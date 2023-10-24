<?php

namespace Company\Domain\Model\Personnel;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class ManagerData extends AbstractEntityMutationPayload
{

    public string $personnelId;

    public function setPersonnelId(string $personnelId)
    {
        $this->personnelId = $personnelId;
        return $this;
    }
}
