<?php

namespace Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class SalesActivityReportData extends AbstractEntityMutationPayload
{

    public string $salesActivityScheduleId;

    public function setSalesActivityScheduleId(string $salesActivityScheduleId)
    {
        $this->salesActivityScheduleId = $salesActivityScheduleId;
        return $this;
    }

    public function __construct(public string $content)
    {
        
    }
}
