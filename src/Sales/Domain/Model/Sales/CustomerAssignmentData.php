<?php

namespace Sales\Domain\Model\Sales;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class CustomerAssignmentData extends AbstractEntityMutationPayload
{

    public readonly string $customerJourneyId;

    public function setCustomerJourneyId(string $customerJourneyId)
    {
        $this->customerJourneyId = $customerJourneyId;
        return $this;
    }
}
