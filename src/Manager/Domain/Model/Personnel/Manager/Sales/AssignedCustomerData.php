<?php

namespace Manager\Domain\Model\Personnel\Manager\Sales;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class AssignedCustomerData extends AbstractEntityMutationPayload
{

    public string $customerId;

    public function setCustomerId(string $customerId)
    {
        $this->customerId = $customerId;
        return $this;
    }
}
