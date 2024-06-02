<?php

namespace Company\Domain\Model;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;
use SharedContext\Domain\ValueObject\AccountInfoData;

readonly class SalesData extends AbstractEntityMutationPayload
{

    public ?string $areaId;
    public ?AccountInfoData $accountInfoData;
    public ?string $type;

    public function setAreaId(?string $areaId)
    {
        $this->areaId = $areaId;
        return $this;
    }

    public function setAccountInfoData(?AccountInfoData $accountInfoData)
    {
        $this->accountInfoData = $accountInfoData;
        return $this;
    }

    public function setType(?string $type)
    {
        $this->type = $type;
        return $this;
    }
}
