<?php

namespace Company\Domain\Model;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;
use Shared\Domain\ValueObject\AccountInfoData;

readonly class AdminData extends AbstractEntityMutationPayload
{

    public function __construct(public AccountInfoData $accountInfoData, public bool $aSuperUser)
    {
        
    }
}
