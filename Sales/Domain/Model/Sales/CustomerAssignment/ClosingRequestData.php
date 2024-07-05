<?php

namespace Sales\Domain\Model\Sales\CustomerAssignment;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class ClosingRequestData extends AbstractEntityMutationPayload
{

    public string $customerAssignmentId;

    public function setCustomerAssignmentId(string $customerAssignmentId)
    {
        $this->customerAssignmentId = $customerAssignmentId;
        return $this;
    }

    public function __construct(public ?int $transactionValue, public ?string $note)
    {
        
    }
}
