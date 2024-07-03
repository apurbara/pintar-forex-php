<?php

namespace Company\Domain\Model\Manager\Sales\CustomerAssignment;

use Company\Domain\Model\Manager\Sales\CustomerAssignment;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Company\Domain\Model\SalesActivity;
use DateTimeImmutable;
use SharedContext\Domain\Enum\SalesActivityScheduleStatus;
use SharedContext\Domain\ValueObject\HourlyTimeInterval;
use Tests\TestBase;

class SalesActivityScheduleTest extends TestBase
{
    protected $salesActivitySchedule;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->salesActivitySchedule = new TestableSalesActivitySchedule();
    }
    
    //
    protected function cancelBySystem()
    {
        $this->salesActivitySchedule->cancelBySystem();
    }
    public function test_cancelBySystem_setStatusCancelledBySystem()
    {
        $this->cancelBySystem();
        $this->assertEquals(SalesActivityScheduleStatus::CANCELLED_BY_SYSTEM, $this->salesActivitySchedule->status);
    }
}

class TestableSalesActivitySchedule extends SalesActivitySchedule
{
    public CustomerAssignment $customerAssignment;
    public SalesActivity $salesActivity;
    public string $id;
    public DateTimeImmutable $createdTime;
    public HourlyTimeInterval $schedule;
    public SalesActivityScheduleStatus $status = SalesActivityScheduleStatus::SCHEDULED;
    public SalesActivityReport $salesActivityReport;
    
    function __construct()
    {
        parent::__construct();
    }
}
