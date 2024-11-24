<?php

namespace Sales\Domain\Model\Sales\CustomerAssignment;

use DateTimeImmutable;
use Sales\Domain\DependencyModel\SalesActivity;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Shared\Domain\Enum\SalesActivityScheduleStatus;
use Shared\Domain\ValueObject\HourlyTimeInterval;
use Shared\Domain\ValueObject\HourlyTimeIntervalData;
use Tests\TestBase;

class SalesActivityScheduleTest extends TestBase
{

    protected $customerAssignment;
    protected $salesActivity;
    protected $salesActivitySchedule, $schedule;
    //
    protected $id = 'newId', $hourlyTimeIntervalData;
    //
    protected $sales;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customerAssignment = $this->buildMockOfClass(CustomerAssignment::class);
        $this->salesActivity = $this->buildMockOfClass(SalesActivity::class);

        $this->hourlyTimeIntervalData = new HourlyTimeIntervalData('next week');
        $data = (new SalesActivityScheduleData(new HourlyTimeIntervalData('tomorrow')))->setId('id');
        $this->salesActivitySchedule = new TestableSalesActivitySchedule($this->customerAssignment,
                $this->salesActivity, 'id', $data);
        
        $this->schedule = $this->buildMockOfClass(HourlyTimeInterval::class);
        $this->salesActivitySchedule->schedule = $this->schedule;
        //
        $this->sales = $this->buildMockOfClass(Sales::class);
    }

    //
    protected function createData()
    {
        return (new SalesActivityScheduleData($this->hourlyTimeIntervalData));
    }
    
    //
    protected function construct()
    {
        return new TestableSalesActivitySchedule($this->customerAssignment, $this->salesActivity, $this->id, $this->createData());
    }
    public function test_construct_setProperties()
    {
        $schedule = $this->construct();
        $this->assertSame($this->customerAssignment, $schedule->customerAssignment);
        $this->assertSame($this->salesActivity, $schedule->salesActivity);
        $this->assertSame($this->id, $schedule->id);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($schedule->createdTime);
        $this->assertInstanceOf(HourlyTimeInterval::class, $schedule->schedule);
        $this->assertEquals(SalesActivityScheduleStatus::SCHEDULED, $schedule->status);
    }
    public function test_construct_assertSalesActivityActive()
    {
        $this->salesActivity->expects($this->once())
                ->method('assertActive');
        $this->construct();
    }
    
    //
    protected function markAsCompleted()
    {
        $this->salesActivitySchedule->markAsCompleted();
    }
    public function test_markAsCompleted_setCompleted()
    {
        $this->markAsCompleted();
        $this->assertSame(SalesActivityScheduleStatus::COMPLETED, $this->salesActivitySchedule->status);
    }
    
    //
    protected function assertIncomplete()
    {
        $this->salesActivitySchedule->assertIncomplete();
    }
    public function test_assertIncomplete_nonScheduled_forbidden()
    {
        $this->salesActivitySchedule->status = SalesActivityScheduleStatus::COMPLETED;
        $this->assertRegularExceptionThrowed(fn() => $this->assertIncomplete(), 'Forbidden', 'schedule already completed');
    }
    public function test_assertIncompleted_scheduledStatus_void()
    {
        $this->assertIncomplete();
        $this->markAsSuccess();
    }
    
    //
    protected function assertBelongsToSales()
    {
        $this->salesActivitySchedule->assertBelongsToSales($this->sales);
    }
    public function test_assertBelongsToSales_customerAssignmentDoesNotBelongsToSales_forbidden()
    {
        $this->assertRegularExceptionThrowed(fn() => $this->assertBelongsToSales(), 'Forbidden', 'unmanaged schedule');
    }
    public function test_assertBelongsToSales_customerAssignmentBelongsToSales_void()
    {
        $this->customerAssignment->expects($this->once())
                ->method('isBelongsToSales')
                ->with($this->sales)
                ->willReturn(true);
        $this->assertBelongsToSales();
    }
    
}

class TestableSalesActivitySchedule extends SalesActivitySchedule
{

    public CustomerAssignment $customerAssignment;
    public SalesActivity $salesActivity;
    public string $id;
    public DateTimeImmutable $createdTime;
    public HourlyTimeInterval $schedule;
    public SalesActivityScheduleStatus $status;
}
