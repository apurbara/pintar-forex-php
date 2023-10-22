<?php

namespace Company\Domain\Model;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;
use SharedContext\Domain\ValueObject\AccountInfoData;

readonly class PersonnelData extends AbstractEntityMutationPayload
{
    public function __construct(public AccountInfoData $accountInfoData)
    {
    }
}
