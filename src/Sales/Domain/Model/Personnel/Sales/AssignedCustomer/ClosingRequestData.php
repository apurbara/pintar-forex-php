<?php

namespace Sales\Domain\Model\Personnel\Sales\AssignedCustomer;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class ClosingRequestData extends AbstractEntityMutationPayload
{

    public string $assignedCustomerId;

    public function setAssignedCustomerId(string $assignedCustomerId)
    {
        $this->assignedCustomerId = $assignedCustomerId;
        return $this;
    }

    public function __construct(public ?int $transactionValue, public ?string $note)
    {
        
    }
}
