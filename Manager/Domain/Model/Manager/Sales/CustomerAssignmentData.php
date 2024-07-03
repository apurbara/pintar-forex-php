<?php

namespace Manager\Domain\Model\Manager\Sales;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class CustomerAssignmentData extends AbstractEntityMutationPayload
{

    public string $salesId;
    public string $customerId;

    public function setSalesIdId(string $salesId)
    {
        $this->salesId = $salesId;
        return $this;
    }

    public function setCustomerId(string $customerId)
    {
        $this->customerId = $customerId;
        return $this;
    }
}
