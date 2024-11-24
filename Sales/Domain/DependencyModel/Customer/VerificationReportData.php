<?php

namespace Sales\Domain\DependencyModel\Customer;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class VerificationReportData extends AbstractEntityMutationPayload
{

    public ?string $factFindingAssignmentId;
    public ?string $customerVerificationId;
    public ?string $note;

    public function setFactFindingAssignmentId(?string $factFindingAssignmentId)
    {
        $this->factFindingAssignmentId = $factFindingAssignmentId;
        return $this;
    }

    public function setCustomerVerificationId(?string $customerVerificationId)
    {
        $this->customerVerificationId = $customerVerificationId;
        return $this;
    }

    public function setNote(?string $note)
    {
        $this->note = $note;
        return $this;
    }
}
