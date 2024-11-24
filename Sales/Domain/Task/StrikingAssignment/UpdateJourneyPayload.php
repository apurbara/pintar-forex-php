<?php

namespace Sales\Domain\Task\StrikingAssignment;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class UpdateJourneyPayload extends AbstractEntityMutationPayload
{

    public ?string $customerJourneyId;

    public function setCustomerJourneyId(?string $customerJourneyId)
    {
        $this->customerJourneyId = $customerJourneyId;
        return $this;
    }
}
