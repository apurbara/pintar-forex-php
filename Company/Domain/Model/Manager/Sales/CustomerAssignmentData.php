<?php

namespace Company\Domain\Model\Manager\Sales;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class CustomerAssignmentData extends AbstractEntityMutationPayload
{

    public string $salesIdId;
    public string $customerId;

    public function setSalesIdId(string $salesIdId)
    {
        $this->salesIdId = $salesIdId;
        return $this;
    }

    public function setCustomerId(string $customerId)
    {
        $this->customerId = $customerId;
        return $this;
    }
}
