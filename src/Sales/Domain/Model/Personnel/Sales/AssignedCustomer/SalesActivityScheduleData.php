<?php

namespace Sales\Domain\Model\Personnel\Sales\AssignedCustomer;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;
use SharedContext\Domain\ValueObject\HourlyTimeIntervalData;

readonly class SalesActivityScheduleData extends AbstractEntityMutationPayload
{

    public string $assignedCustomerId;
    public string $salesActivityId;

    public function setAssignedCustomerId(string $assignedCustomerId)
    {
        $this->assignedCustomerId = $assignedCustomerId;
        return $this;
    }

    public function setSalesActivityId(string $salesActivityId)
    {
        $this->salesActivityId = $salesActivityId;
        return $this;
    }

    public function __construct(public HourlyTimeIntervalData $hourlyTimeIntervalData)
    {
        
    }
}
