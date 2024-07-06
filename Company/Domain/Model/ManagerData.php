<?php

namespace Company\Domain\Model;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;
use Shared\Domain\ValueObject\AccountInfoData;

readonly class ManagerData extends AbstractEntityMutationPayload
{
    public function __construct(public AccountInfoData $accountInfoData)
    {
    }
}
