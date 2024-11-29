<?php

namespace Sales\Domain\Model\Sales;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sales\Domain\DependencyModel\Customer;
use Sales\Domain\DependencyModel\CustomerJourney;
use Sales\Domain\DependencyModel\SalesActivity;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReportData;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivityScheduleData;
use Sales\Domain\Model\Sales\StrikingAssignment\ClosingRequest;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Shared\Domain\ValueObject\HourlyTimeIntervalData;
use Tests\TestBase;

class StrikingAssignmentTest extends TestBase
{

    protected $sales, $customer, $customerAssignment;
    protected $strikingAssignment;
    protected $closingRequest;
    //
    protected $rating = 3;
    protected $customerJourney;
    protected $salesActivity;
    protected $reportId = 'reportId', $salesActivityReportData;
    protected $scheduleId = 'scheduleId', $salesActivityScheduleData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = $this->buildMockOfClass(Sales::class);
        $this->customer = $this->buildMockOfClass(Customer::class);
        $this->customerAssignment = $this->buildMockOfClass(CustomerAssignment::class);

        $this->strikingAssignment = new TestableStrikingAssignment();
        $this->strikingAssignment->sales = $this->sales;
        $this->strikingAssignment->customer = $this->customer;
        $this->strikingAssignment->customerAssignment = $this->customerAssignment;
        
        $this->closingRequest = $this->buildMockOfClass(ClosingRequest::class);
        $this->strikingAssignment->closingRequests = new ArrayCollection();
        $this->strikingAssignment->closingRequests->add($this->closingRequest);
        //
        $this->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
        
        $this->salesActivity = $this->buildMockOfClass(SalesActivity::class);
        $this->salesActivityReportData = new SalesActivityReportData('content');
        $this->salesActivityScheduleData = new SalesActivityScheduleData(new HourlyTimeIntervalData('new week'));
    }
    
    //
    protected function updateCustomerRating()
    {
        $this->strikingAssignment->updateCustomerRating($this->rating);
    }
    public function test_updateCustomerRating_updateCustomer()
    {
        $this->customer->expects($this->once())
                ->method('updateRating')
                ->with($this->rating);
        $this->updateCustomerRating();
    }
    public function test_updateCustomerRating_inactiveAssignment_forbidden()
    {
        $this->strikingAssignment->status = CustomerAssignmentStatus::COMPLETED;
        $this->assertRegularExceptionThrowed(fn() => $this->updateCustomerRating(), 'Forbidden', 'inactive assignment');
    }
    
    //
    protected function updateCustomerJourney()
    {
        $this->strikingAssignment->updateJourney($this->customerJourney);
    }
    public function test_updateCustomerJourney_setCustomerJourney()
    {
        $this->updateCustomerJourney();
        $this->assertSame($this->customerJourney, $this->strikingAssignment->customerJourney);
    }
    public function test_updateCustomerJourney_assertCustomerJourneyActive()
    {
        $this->customerJourney->expects($this->once())
                ->method('assertActive');
        $this->updateCustomerJourney();
    }
    public function test_updateCustomerJourney_inactiveAssignment_forbidden()
    {
        $this->strikingAssignment->status = CustomerAssignmentStatus::COMPLETED;
        $this->assertRegularExceptionThrowed(fn() => $this->updateCustomerJourney(), 'Forbidden', 'inactive assignment');
    }
    
    //
    protected function isBelongsToSales()
    {
        return $this->strikingAssignment->isBelongsToSales($this->sales);
    }
    public function test_isBelongsToSales_sameSales_returnTrue()
    {
        $this->assertTrue($this->isBelongsToSales());
    }
    public function test_isBelongsToSales_diffSales_returnFalse()
    {
        $this->strikingAssignment->sales = $this->buildMockOfClass(Sales::class);
        $this->assertFalse($this->isBelongsToSales());
    }

    //
    protected function assertBelongsToSales()
    {
        $this->strikingAssignment->assertBelongsToSales($this->sales);
    }
    public function test_assertBelongsToSales_differentSales_forbidden()
    {
        $this->strikingAssignment->sales = $this->buildMockOfClass(Sales::class);
        $this->assertRegularExceptionThrowed(fn() => $this->assertBelongsToSales(), 'Forbidden', 'unmanaged assignment');
    }
    public function test_assertBelongsToSales_sameSales_void()
    {
        $this->assertBelongsToSales();
        $this->markAsSuccess();
    }
    
    //
    protected function assertNoPendingClosingRequest()
    {
        $this->closingRequest->expects($this->any())
                ->method('getStatus')
                ->willReturn(ManagementApprovalStatus::REJECTED);
        $this->strikingAssignment->assertNoPendingClosingRequest();
    }
    public function test_assertNoPendingClosingRequest_containOngoinClosingRequest_forbidden()
    {
        $this->closingRequest->expects($this->any())
                ->method('getStatus')
                ->willReturn(ManagementApprovalStatus::WAITING_FOR_APPROVAL);
        $this->assertRegularExceptionThrowed(fn() => $this->assertNoPendingClosingRequest(), 'Forbidden', 'this assignment has pending closing request');
    }
    public function test_assertNoPendingClosingRequest_noPendingRequest_void()
    {
        $this->assertNoPendingClosingRequest();
        $this->markAsSuccess();
    }
    
    //
    protected function submitNonScheduledSalesActivityReport()
    {
        return $this->strikingAssignment->submitNonScheduledSalesActivityReport($this->salesActivity, $this->reportId,
                        $this->salesActivityReportData);
    }
    public function test_submitNonScheduleSalesActivityReport_returnReportCreatedInCustomerAssignment()
    {
        $this->customerAssignment->expects($this->once())
                ->method('submitNonScheduledSalesActivityReport')
                ->with($this->salesActivity, $this->reportId, $this->salesActivityReportData);
        $this->assertInstanceOf(SalesActivityReport::class, $this->submitNonScheduledSalesActivityReport());
    }
    public function test_submitNonScheduledSalesActivityReport_inactiveAssignment_forbidden()
    {
        $this->strikingAssignment->status = CustomerAssignmentStatus::COMPLETED;
        $this->assertRegularExceptionThrowed(fn() => $this->submitNonScheduledSalesActivityReport(), 'Forbidden',
                'inactive assignment');
    }

    //
    protected function submitSalesActivitySchedule()
    {
        $hourlyTimeIntervalData = new HourlyTimeIntervalData('next week');
        $scheduledSalesActivityData = (new SalesActivityScheduleData($hourlyTimeIntervalData))->setId('scheduleId');
        return $this->strikingAssignment->submitSalesActivitySchedule($this->salesActivity, $this->scheduleId,
                        $scheduledSalesActivityData);
    }
    public function test_submitSalesActivitySchedule_returnScheduledSalesActivity()
    {
        $this->assertInstanceOf(SalesActivitySchedule::class, $this->submitSalesActivitySchedule());
    }
    public function test_submitSalesActivitySchedule_inactiveAssignment_forbidden()
    {
        $this->strikingAssignment->status = CustomerAssignmentStatus::COMPLETED;
        $this->assertRegularExceptionThrowed(fn() => $this->submitSalesActivitySchedule(), 'Forbidden',
                'inactive assignment');
    }
}

class TestableStrikingAssignment extends StrikingAssignment
{

    public Sales $sales;
    public Customer $customer;
    public string $id = 'strikingAssignmentId';
    public CustomerAssignmentStatus $status;
    public CustomerAssignment $customerAssignment;
    public Collection $closingRequests;
    public ?CustomerJourney $customerJourney;

    public function __construct()
    {
        parent::__construct();
        $this->status = CustomerAssignmentStatus::ACTIVE;
    }
}
