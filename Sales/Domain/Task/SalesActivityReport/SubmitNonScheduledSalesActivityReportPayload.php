<?php

namespace Sales\Domain\Task\SalesActivityReport;

use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReportData;

readonly class SubmitNonScheduledSalesActivityReportPayload extends SalesActivityReportData
{

    public ?string $customerAssignmentId;
    public ?string $salesActivityId;

    public function setCustomerAssignmentId(?string $customerAssignmentId)
    {
        $this->customerAssignmentId = $customerAssignmentId;
        return $this;
    }

    public function setSalesActivityId(?string $salesActivityId): self
    {
        $this->salesActivityId = $salesActivityId;
        return $this;
    }
}
