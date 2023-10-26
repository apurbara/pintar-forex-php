<?php

namespace Sales\Domain\Model\Personnel\Sales\ManagedCustomer\ScheduledSalesActivity;

use DateTimeImmutable;
use Sales\Domain\Model\Personnel\Sales\ManagedCustomer\ScheduledSalesActivity;

class ScheduledSalesActivityReport
{

    protected ScheduledSalesActivity $scheduledSalesActivity;
    protected string $id;
    protected DateTimeImmutable $submitTime;
    protected string $report;

    public function __construct(
//            ScheduledSalesActivity $scheduledSalesActivity, string $id, DateTimeImmutable $submitTime, string $report
    )
    {
        $this->scheduledSalesActivity = $scheduledSalesActivity;
        $this->id = $id;
        $this->submitTime = $submitTime;
        $this->report = $report;
    }
}
