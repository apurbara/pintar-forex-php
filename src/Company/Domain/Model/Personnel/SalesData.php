<?php

namespace Company\Domain\Model\Personnel;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class SalesData extends AbstractEntityMutationPayload
{

    public string $personnelId;
    public string $areaId;

    public function __construct(public string $type)
    {
        
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
