<?php

namespace Sales\Domain\Model\Sales;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Sales\Domain\DependencyModel\SalesActivity;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Tests\TestBase;

class CustomerAssignmentTest extends TestBase
{

    protected $customerAssignment;
    protected $greetingAssignment, $factFindingAssignment, $strikingAssignment;
    //
    protected $salesActivity;
    protected $reportId = 'reportId', $salesActivityReportData;
    //
    protected $sales;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customerAssignment = new TestableCustomerAssignment();
        $this->customerAssignment->greetingAssignment = null;
        $this->customerAssignment->factFindingAssignment = null;
        $this->customerAssignment->strikingAssignment = null;
        
        $this->greetingAssignment = $this->buildMockOfClass(GreetingAssignment::class);
        $this->factFindingAssignment = $this->buildMockOfClass(FactFindingAssignment::class);
        $this->strikingAssignment = $this->buildMockOfClass(StrikingAssignment::class);
        
        $this->customerAssignment->greetingAssignment = $this->greetingAssignment;
        //
        $this->salesActivity = $this->buildMockOfClass(SalesActivity::class);
        $this->salesActivityReportData = new SalesActivitySchedule\SalesActivityReportData('content');
        //
        $this->sales = $this->buildMockOfClass(Sales::class);
    }

    //
    protected function submitNonScheduledSalesActivityReport()
    {
        return $this->customerAssignment->submitNonScheduledSalesActivityReport(
                        $this->salesActivity, $this->reportId, $this->salesActivityReportData);
    }
    public function test_submitNonScheduledSalesActivityReport_returnSalesActivityReport()
    {
        $this->assertInstanceOf(SalesActivityReport::class, $this->submitNonScheduledSalesActivityReport());
    }
    
    //
    protected function isBelongsToSales()
    {
        return $this->customerAssignment->isBelongsToSales($this->sales);
    }
    public function test_isBelongsToSales_aGreetingAssignment_returnGreetingAssignmentComparisonResult()
    {
        $this->greetingAssignment->expects($this->once())
                ->method('isBelongsToSales')
                ->with($this->sales)
                ->willReturn(true);
        $this->assertTrue($this->isBelongsToSales());
    }
    public function test_isBelongsToSales_aFactFindingAssignment_returnFactFindingAssignmentComparisonResult()
    {
        $this->customerAssignment->greetingAssignment = null;
        $this->customerAssignment->factFindingAssignment = $this->factFindingAssignment;
        $this->factFindingAssignment->expects($this->once())
                ->method('isBelongsToSales')
                ->with($this->sales)
                ->willReturn(true);
        $this->assertTrue($this->isBelongsToSales());
    }
    public function test_isBelongsToSales_aStrikingAssignment_returnStrikingAssignmentComparisonResult()
    {
        $this->customerAssignment->greetingAssignment = null;
        $this->customerAssignment->factFindingAssignment = null;
        $this->customerAssignment->strikingAssignment = $this->strikingAssignment;
        $this->strikingAssignment->expects($this->once())
                ->method('isBelongsToSales')
                ->with($this->sales)
                ->willReturn(true);
        $this->assertTrue($this->isBelongsToSales());
    }
}

class TestableCustomerAssignment extends CustomerAssignment
{
    public ?GreetingAssignment $greetingAssignment;
    public ?FactFindingAssignment $factFindingAssignment;
    public ?StrikingAssignment $strikingAssignment;
    public string $id;
    public DateTimeImmutable $createdTime;
    public Collection $salesActivitySchedules;

    public function __construct()
    {
        parent::__construct();
    }
}
