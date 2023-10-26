<?php

namespace Sales\Domain\Model\Personnel\Sales\AssignedCustomer;

use DateTimeImmutable;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\SalesActivity;
use SharedContext\Domain\Enum\ScheduledSalesActivityStatus;
use SharedContext\Domain\ValueObject\HourlyTimeInterval;
use SharedContext\Domain\ValueObject\HourlyTimeIntervalData;
use Tests\TestBase;

class SalesActivityScheduleTest extends TestBase
{

    protected $assignedCustomer;
    protected $salesActivity;
    protected $scheduledSalesActivity;
    //
    protected $id = 'newId', $hourlyTimeIntervalData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assignedCustomer = $this->buildMockOfClass(AssignedCustomer::class);
        $this->salesActivity = $this->buildMockOfClass(SalesActivity::class);

        $this->hourlyTimeIntervalData = new HourlyTimeIntervalData('next week');
        $data = (new SalesActivityScheduleData(new HourlyTimeIntervalData('tomorrow')))->setId('id');
        $this->scheduledSalesActivity = new TestableSalesActivitySchedule($this->assignedCustomer,
                $this->salesActivity, $data);
    }

    //
    protected function createData()
    {
        return (new SalesActivityScheduleData($this->hourlyTimeIntervalData))
                ->setId($this->id);
    }
    
    //
    protected function construct()
    {
        return new TestableSalesActivitySchedule($this->assignedCustomer, $this->salesActivity, $this->createData());
    }
    public function test_construct_setProperties()
    {
        $schedule = $this->construct();
        $this->assertSame($this->assignedCustomer, $schedule->assignedCustomer);
        $this->assertSame($this->salesActivity, $schedule->salesActivity);
        $this->assertSame($this->id, $schedule->id);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($schedule->createdTime);
        $this->assertInstanceOf(HourlyTimeInterval::class, $schedule->schedule);
        $this->assertEquals(ScheduledSalesActivityStatus::SCHEDULED, $schedule->status);
    }
    public function test_construct_assertSalesActivityActive()
    {
        $this->salesActivity->expects($this->once())
                ->method('assertActive');
        $this->construct();
    }
}

class TestableSalesActivitySchedule extends SalesActivitySchedule
{

    public AssignedCustomer $assignedCustomer;
    public SalesActivity $salesActivity;
    public string $id;
    public DateTimeImmutable $createdTime;
    public HourlyTimeInterval $schedule;
    public ScheduledSalesActivityStatus $status;
}
