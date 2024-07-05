<?php

namespace Sales\Domain\Task\SalesActivityReport;

use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReportData;

readonly class SubmitInitialSalesActivityReportPayload extends SalesActivityReportData
{

    public ?string $customerAssignmentId;

    public function setCustomerAssignmentId(?string $customerAssignmentId)
    {
        $this->customerAssignmentId = $customerAssignmentId;
        return $this;
    }
}
