<?php

namespace Sales\Domain\Model\AreaStructure\Area\Customer;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class VerificationReportData extends AbstractEntityMutationPayload
{

    public string $assignedCustomerId;
    public string $customerVerificationId;

    public function setAssignedCustomerId(string $assignedCustomerId)
    {
        $this->assignedCustomerId = $assignedCustomerId;
        return $this;
    }

    public function setCustomerVerificationId(string $customerVerificationId)
    {
        $this->customerVerificationId = $customerVerificationId;
        return $this;
    }

    public function __construct(public ?string $note)
    {
        
    }
}
