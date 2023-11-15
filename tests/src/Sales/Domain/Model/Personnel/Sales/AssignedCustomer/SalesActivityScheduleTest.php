<?php

namespace Sales\Domain\Model\Personnel\Sales\AssignedCustomer;

use DateTimeImmutable;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule\SalesActivityReport;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule\SalesActivityReportData;
use Sales\Domain\Model\SalesActivity;
use Sales\Domain\Service\SalesActivitySchedulerService;
use SharedContext\Domain\Enum\SalesActivityScheduleStatus;
use SharedContext\Domain\ValueObject\HourlyTimeInterval;
use SharedContext\Domain\ValueObject\HourlyTimeIntervalData;
use Tests\TestBase;

class SalesActivityScheduleTest extends TestBase
{

    protected $assignedCustomer;
    protected $salesActivity;
    protected $salesActivitySchedule, $schedule;
    //
    protected $id = 'newId', $hourlyTimeIntervalData;
    //
    protected $sales;
    //
    protected $schedulerService, $startTime;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assignedCustomer = $this->buildMockOfClass(AssignedCustomer::class);
        $this->salesActivity = $this->buildMockOfClass(SalesActivity::class);

        $this->hourlyTimeIntervalData = new HourlyTimeIntervalData('next week');
        $data = (new SalesActivityScheduleData(new HourlyTimeIntervalData('tomorrow')))->setId('id');
        $this->salesActivitySchedule = new TestableSalesActivitySchedule($this->assignedCustomer,
                $this->salesActivity, $data);
        
        $this->schedule = $this->buildMockOfClass(HourlyTimeInterval::class);
        $this->salesActivitySchedule->schedule = $this->schedule;
        //
        $this->sales = $this->buildMockOfClass(Sales::class);
        //
        $this->schedulerService = $this->buildMockOfClass(SalesActivitySchedulerService::class);
        $this->startTime = new DateTimeImmutable('tomorrow');
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
        $this->assertEquals(SalesActivityScheduleStatus::SCHEDULED, $schedule->status);
    }
    public function test_construct_assertSalesActivityActive()
    {
        $this->salesActivity->expects($this->once())
                ->method('assertActive');
        $this->construct();
    }
    
    //
    protected function getActivityDuration()
    {
        return $this->salesActivitySchedule->getActivityDuration();
    }
    public function test_getActivityDuration_returnSalesActivityDuration()
    {
        $this->salesActivity->expects($this->once())
                ->method('getDuration')
                ->willReturn(7);
        $this->assertSame(7, $this->getActivityDuration());
    }
    
    //
    protected function assertBelongsToSales()
    {
        $this->salesActivitySchedule->assertBelongsToSales($this->sales);
    }
    public function test_assertBelongsToSales_assetAssignedCustomerBelongsToSales()
    {
        $this->assignedCustomer->expects($this->once())
                ->method('assertBelongsToSales')
                ->with($this->sales);
        $this->assertBelongsToSales();
    }
    
    //
    protected function submitReport()
    {
        $salesActivityReportData = (new SalesActivityReportData('report content'))->setId('reportId');
        return $this->salesActivitySchedule->submitReport($salesActivityReportData);
    }
    public function test_submitReport_returnReport()
    {
        $this->assertInstanceOf(SalesActivityReport::class, $this->submitReport());
    }
    public function test_submitReport_setStatusCompleted()
    {
        $this->submitReport();
        $this->assertSame(SalesActivityScheduleStatus::COMPLETED, $this->salesActivitySchedule->status);
    }
    public function test_submitReport_nonScheduledStatus_forbidden()
    {
        $this->salesActivitySchedule->status = SalesActivityScheduleStatus::COMPLETED;
        $this->assertRegularExceptionThrowed(fn() => $this->submitReport(), 'Forbidden', 'schedule concluded');
    }
    
    //
    protected function includeInSchedulerService()
    {
        $this->schedule->expects($this->any())
                ->method('getStartTime')
                ->willReturn($this->startTime);
        $this->salesActivitySchedule->includeInSchedulerService($this->schedulerService);
    }
    public function test_includeInSchedulerService_addToScheduler()
    {
        $this->schedulerService->expects($this->once())
                ->method('add')
                ->with($this->startTime, $this->salesActivitySchedule);
        $this->includeInSchedulerService();
    }
    public function test_includeInSchedulerService_notUpcomingScheduler_excludeFromScheduler()
    {
        $this->startTime = new DateTimeImmutable('yesterday');
        $this->schedulerService->expects($this->never())
                ->method('add')
                ->with($this->startTime, $this->salesActivitySchedule);
        $this->includeInSchedulerService();
    }
}

class TestableSalesActivitySchedule extends SalesActivitySchedule
{

    public AssignedCustomer $assignedCustomer;
    public SalesActivity $salesActivity;
    public string $id;
    public DateTimeImmutable $createdTime;
    public HourlyTimeInterval $schedule;
    public SalesActivityScheduleStatus $status;
}
