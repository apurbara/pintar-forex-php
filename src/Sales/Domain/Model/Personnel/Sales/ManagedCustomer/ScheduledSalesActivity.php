<?php

namespace Sales\Domain\Model\Personnel\Sales\AssignedCustomer;

use DateTimeImmutable;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\SalesActivity;
use SharedContext\Domain\Enum\ScheduledSalesActivityStatus;
use SharedContext\Domain\ValueObject\HourlyTimeInterval;

class ScheduledSalesActivity
{

    protected AssignedCustomer $managedCustomer;
    protected SalesActivity $salesActivity;
    protected string $id;
    protected DateTimeImmutable $createdTime;
    protected HourlyTimeInterval $schedule;
    protected ScheduledSalesActivityStatus $status;

    public function __construct(
//            ManagedCustomer $managedCustomer, SalesActivity $salesActivity, string $id, bool $cancelled,
//            DateTimeImmutable $createdTime, HourlyTimeInterval $schedule
    )
    {
//        $this->managedCustomer = $managedCustomer;
//        $this->salesActivity = $salesActivity;
//        $this->id = $id;
//        $this->cancelled = $cancelled;
//        $this->createdTime = $createdTime;
//        $this->schedule = $schedule;
    }

    //
    public function submitReport()
    {
        
    }
}
