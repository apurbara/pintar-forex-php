<?php

namespace Sales\Domain\Model\AreaStructure\Area;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class CustomerData extends AbstractEntityMutationPayload
{

    public string $areaId;

    public function setAreaId(string $areaId)
    {
        $this->areaId = $areaId;
        return $this;
    }

    public function __construct(public string $name, public string $email, public string $phone)
    {
        
    }
}
