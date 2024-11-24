<?php

namespace Company\Domain\Model\Manager\Sales\CustomerAssignment;

use Shared\Domain\Enum\SalesActivityScheduleStatus;
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
    public function test_cancelBySystem_setCancelled()
    {
        $this->cancelBySystem();
        $this->assertEquals(SalesActivityScheduleStatus::CANCELLED_BY_SYSTEM, $this->salesActivitySchedule->status);
    }
    public function test_cancelBySystem_ConcludedSchedule_NOP()
    {
        $this->salesActivitySchedule->status = SalesActivityScheduleStatus::COMPLETED;
        $this->cancelBySystem();
        $this->assertEquals(SalesActivityScheduleStatus::COMPLETED, $this->salesActivitySchedule->status);
    }


}

class TestableSalesActivitySchedule extends SalesActivitySchedule
{
    public SalesActivityScheduleStatus $status = SalesActivityScheduleStatus::SCHEDULED;

    function __construct()
    {
        parent::__construct();
    }
}
