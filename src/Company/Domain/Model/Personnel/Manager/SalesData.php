<?php

namespace Company\Domain\Model\Personnel\Manager;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class SalesData extends AbstractEntityMutationPayload
{

    public string $managerId;
    public string $personnelId;
    public string $areaId;

    public function __construct(public string $type)
    {
        
    }

    public function setManagerId(string $managerId)
    {
        $this->managerId = $managerId;
        return $this;
    }

    public function setPersonnelId(string $personnelId)
    {
        $this->personnelId = $personnelId;
        return $this;
    }

    public function setAreaId(string $areaId)
    {
        $this->areaId = $areaId;
        return $this;
    }
}
