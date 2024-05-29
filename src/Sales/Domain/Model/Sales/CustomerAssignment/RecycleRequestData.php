<?php

namespace Sales\Domain\Model\Sales\CustomerAssignment;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class RecycleRequestData extends AbstractEntityMutationPayload
{

    public string $customerAssignmentId;

    public function setCustomerAssignmentId(string $customerAssignmentId)
    {
        $this->customerAssignmentId = $customerAssignmentId;
        return $this;
    }

    public function __construct(public ?string $note)
    {
        
    }
}
