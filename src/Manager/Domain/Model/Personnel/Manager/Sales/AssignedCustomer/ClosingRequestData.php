<?php

namespace Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class ClosingRequestData extends AbstractEntityMutationPayload
{
    public string $remark;
}
