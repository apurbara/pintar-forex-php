<?php

namespace Company\Domain\Model\Manager;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;
use SharedContext\Domain\ValueObject\AccountInfoData;

readonly class SalesData extends AbstractEntityMutationPayload
{

    public ?string $managerId;
    public ?string $cityId;
    public ?AccountInfoData $accountInfoData;
    public ?string $type;

    public function setManagerId(?string $managerId)
    {
        $this->managerId = $managerId;
        return $this;
    }

    public function setCityId(?string $cityId)
    {
        $this->cityId = $cityId;
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
