<?php

namespace Sales\Domain\Task\SalesActivityReport;

use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReportData;

readonly class SubmitNonScheduleSalesActivityReportPayload extends SalesActivityReportData
{

    public ?string $salesActivityId;
    public ?string $customerAssignmentId;

    public function setCustomerAssignmentId(?string $customerAssignmentId)
    {
        $this->customerAssignmentId = $customerAssignmentId;
        return $this;
    }

    public function setSalesActivityId(?string $salesActivityId)
    {
        $this->salesActivityId = $salesActivityId;
        return $this;
    }
}
