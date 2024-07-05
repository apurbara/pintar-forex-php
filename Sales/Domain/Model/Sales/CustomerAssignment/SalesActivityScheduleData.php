<?php

namespace Sales\Domain\Model\Sales\CustomerAssignment;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReportData;
use SharedContext\Domain\ValueObject\HourlyTimeIntervalData;

readonly class SalesActivityScheduleData extends AbstractEntityMutationPayload
{

    public string $customerAssignmentId;
    public string $salesActivityId;
    public SalesActivityReportData $salesActivityReportData;

    public function setCustomerAssignmentId(string $customerAssignmentId)
    {
        $this->customerAssignmentId = $customerAssignmentId;
        return $this;
    }

    public function setSalesActivityId(string $salesActivityId)
    {
        $this->salesActivityId = $salesActivityId;
        return $this;
    }

    public function setSalesActivityReportData(SalesActivityReportData $salesActivityReportData)
    {
        $this->salesActivityReportData = $salesActivityReportData;
        return $this;
    }

    public function __construct(public HourlyTimeIntervalData $hourlyTimeIntervalData)
    {
        
    }
}
