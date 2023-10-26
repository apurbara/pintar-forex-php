<?php

namespace Sales\Domain\Model\Personnel\Sales\AssignedCustomer;

use DateTimeImmutable;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule\SalesActivityReport;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule\SalesActivityReportData;
use Sales\Domain\Model\SalesActivity;
use SharedContext\Domain\Enum\SalesActivityScheduleStatus;
use SharedContext\Domain\ValueObject\HourlyTimeInterval;
use SharedContext\Domain\ValueObject\HourlyTimeIntervalData;
use Tests\TestBase;

class SalesActivityScheduleTest extends TestBase
{

    protected $assignedCustomer;
    protected $salesActivity;
    protected $salesActivitySchedule;
    //
    protected $id = 'newId', $hourlyTimeIntervalData;
    //
    protected $sales;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assignedCustomer = $this->buildMockOfClass(AssignedCustomer::class);
        $this->salesActivity = $this->buildMockOfClass(SalesActivity::class);

        $this->hourlyTimeIntervalData = new HourlyTimeIntervalData('next week');
        $data = (new SalesActivityScheduleData(new HourlyTimeIntervalData('tomorrow')))->setId('id');
        $this->salesActivitySchedule = new TestableSalesActivitySchedule($this->assignedCustomer,
                $this->salesActivity, $data);
        //
        $this->sales = $this->buildMockOfClass(Sales::class);
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
    public function test_submitReport_nonScheduledStatus_forbidden()
    {
        $this->salesActivitySchedule->status = SalesActivityScheduleStatus::COMPLETED;
        $this->assertRegularExceptionThrowed(fn() => $this->submitReport(), 'Forbidden', 'schedule concluded');
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
