<?php

namespace Sales\Domain\DependencyModel\AreaStructure\Area\Customer;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class VerificationReportData extends AbstractEntityMutationPayload
{

    public string $customerAssignmentId;
    public string $customerVerificationId;

    public function setCustomerAssignmentId(string $assignedCustomerId)
    {
        $this->customerAssignmentId = $assignedCustomerId;
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
