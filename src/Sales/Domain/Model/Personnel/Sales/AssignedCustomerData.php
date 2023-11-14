<?php

namespace Sales\Domain\Model\Personnel\Sales;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class AssignedCustomerData extends AbstractEntityMutationPayload
{

    public readonly string $customerJourneyId;

    public function setCustomerJourneyId(string $customerJourneyId)
    {
        $this->customerJourneyId = $customerJourneyId;
        return $this;
    }
}
