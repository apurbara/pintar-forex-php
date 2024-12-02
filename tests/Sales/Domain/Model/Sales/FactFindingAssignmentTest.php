<?php

namespace Sales\Domain\Model\Sales;

use Sales\Domain\DependencyModel\Customer;
use Sales\Domain\DependencyModel\Customer\VerificationReportData;
use Sales\Domain\DependencyModel\CustomerVerification;
use Sales\Domain\DependencyModel\SalesActivity;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReportData;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivityScheduleData;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\CustomerStatus;
use Shared\Domain\ValueObject\HourlyTimeIntervalData;
use Tests\TestBase;

class FactFindingAssignmentTest extends TestBase
{

    protected $sales, $customer, $customerAssignment;
    protected $factFindingAssignment;
    //
    protected $customerVerification, $verificationReportData;
    protected $rating = 4;
    //
    protected $salesActivity;
    protected $reportId = 'reportId', $salesActivityReportData;
    protected $scheduleId = 'scheduleId', $salesActivityScheduleData;
    
    protected $allActiveCustomerVerifications;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = $this->buildMockOfClass(Sales::class);
        $this->customer = $this->buildMockOfClass(Customer::class);
        $this->customerAssignment = $this->buildMockOfClass(CustomerAssignment::class);

        $this->factFindingAssignment = new TestableFactFindingAssignment();
        $this->factFindingAssignment->sales = $this->sales;
        $this->factFindingAssignment->customer = $this->customer;
        $this->factFindingAssignment->customerAssignment = $this->customerAssignment;
        //
        $this->customerVerification = $this->buildMockOfClass(CustomerVerification::class);
        $this->verificationReportData = new VerificationReportData('note');

        $this->salesActivity = $this->buildMockOfClass(SalesActivity::class);
        $this->salesActivityReportData = new SalesActivityReportData('content');
        $this->salesActivityScheduleData = new SalesActivityScheduleData(new HourlyTimeIntervalData('new week'));
        //
        $this->allActiveCustomerVerifications = [$this->customerVerification];
    }
    
    //
    protected function isBelongsToSales()
    {
        return $this->factFindingAssignment->isBelongsToSales($this->sales);
    }
    public function test_isBelongsToSales_sameSales_returnTrue()
    {
        $this->assertTrue($this->isBelongsToSales());
    }
    public function test_isBelongsToSales_diffSales_returnFalse()
    {
        $this->factFindingAssignment->sales = $this->buildMockOfClass(Sales::class);
        $this->assertFalse($this->isBelongsToSales());
    }

    //
    protected function assertBelongsToSales()
    {
        $this->factFindingAssignment->assertBelongsToSales($this->sales);
    }
    public function test_assertBelongsToSales_differentSales_forbidden()
    {
        $this->factFindingAssignment->sales = $this->buildMockOfClass(Sales::class);
        $this->assertRegularExceptionThrowed(fn() => $this->assertBelongsToSales(), 'Forbidden', 'unmanaged assignment');
    }
    public function test_assertBelongsToSales_sameSales_void()
    {
        $this->assertBelongsToSales();
        $this->markAsSuccess();
    }

    //
    protected function submitCustomerVerificationReport()
    {
        $this->factFindingAssignment->SubmitCustomerVerificationReport($this->customerVerification,
                $this->verificationReportData);
    }
    public function test_submitVerificationReport_submitCustomerVerificationReport()
    {
        $this->customer->expects($this->once())
                ->method('submitVerificationReport')
                ->with($this->customerVerification, $this->verificationReportData);
        $this->submitCustomerVerificationReport();
    }
    public function test_submitVerificationReport_inactiveAssignment_forbidden()
    {
        $this->factFindingAssignment->status = CustomerAssignmentStatus::COMPLETED;
        $this->assertRegularExceptionThrowed(fn() => $this->submitCustomerVerificationReport(), 'Forbidden',
                'inactive assignment');
    }

    //
    protected function updateCustomerRating()
    {
        $this->factFindingAssignment->updateCustomerRating($this->rating);
    }
    public function test_updateCustomerRating_setCustomerRating()
    {
        $this->customer->expects($this->once())
                ->method('updateRating')
                ->with($this->rating);
        $this->updateCustomerRating();
    }
    public function test_updateCustomerRating_inactiveAssignment_forbidden()
    {
        $this->factFindingAssignment->status = CustomerAssignmentStatus::COMPLETED;
        $this->assertRegularExceptionThrowed(fn() => $this->updateCustomerRating(), 'Forbidden', 'inactive assignment');
    }

    //
    protected function markCustomerVerified()
    {
        $this->factFindingAssignment->markCustomerVerified($this->allActiveCustomerVerifications);
    }
    public function test_markCustomerVerified_updateCustomerStatus()
    {
        $this->customer->expects($this->once())
                ->method('markVerificationComplete')
                ->with($this->allActiveCustomerVerifications);
        $this->markCustomerVerified();
    }
    public function test_markCustomerVerified_setAssignmentCompleted()
    {
        $this->markCustomerVerified();
        $this->assertEquals(CustomerAssignmentStatus::COMPLETED, $this->factFindingAssignment->status);
    }
    public function test_markCustomerVerified_inactiveAssignment_forbidden()
    {
        $this->factFindingAssignment->status = CustomerAssignmentStatus::COMPLETED;
        $this->assertRegularExceptionThrowed(fn() => $this->markCustomerVerified(), 'Forbidden', 'inactive assignment');
    }
    public function test_markCustomerVerified_recordCustomerVerifiedEvent()
    {
        $this->markCustomerVerified();
        $event = new \Sales\Domain\Event\CustomerVerified($this->factFindingAssignment->id);
        $this->assertEquals($event, $this->factFindingAssignment->recordedEvents[0]);
    }
    public function test_markCustomerVerified_completeAssignment()
    {
        $this->customerAssignment->expects($this->once())
                ->method('completeAssignment');
        $this->markCustomerVerified();
    }

    //
    protected function submitNonScheduledSalesActivityReport()
    {
        return $this->factFindingAssignment->submitNonScheduledSalesActivityReport($this->salesActivity, $this->reportId,
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
        $this->factFindingAssignment->status = CustomerAssignmentStatus::COMPLETED;
        $this->assertRegularExceptionThrowed(fn() => $this->submitNonScheduledSalesActivityReport(), 'Forbidden',
                'inactive assignment');
    }

    //
    protected function submitSalesActivitySchedule()
    {
        $hourlyTimeIntervalData = new HourlyTimeIntervalData('next week');
        $scheduledSalesActivityData = (new SalesActivityScheduleData($hourlyTimeIntervalData))->setId('scheduleId');
        return $this->factFindingAssignment->submitSalesActivitySchedule($this->salesActivity, $this->scheduleId,
                        $scheduledSalesActivityData);
    }
    public function test_submitSalesActivitySchedule_returnScheduledSalesActivity()
    {
        $this->assertInstanceOf(SalesActivitySchedule::class, $this->submitSalesActivitySchedule());
    }
    public function test_submitSalesActivitySchedule_inactiveAssignment_forbidden()
    {
        $this->factFindingAssignment->status = CustomerAssignmentStatus::COMPLETED;
        $this->assertRegularExceptionThrowed(fn() => $this->submitSalesActivitySchedule(), 'Forbidden',
                'inactive assignment');
    }
}

class TestableFactFindingAssignment extends FactFindingAssignment
{

    public Sales $sales;
    public Customer $customer;
    public string $id = 'factFindingAssignmentId';
    public CustomerAssignmentStatus $status;
    public CustomerAssignment $customerAssignment;
    public $recordedEvents;

    public function __construct()
    {
        parent::__construct();
        $this->status = CustomerAssignmentStatus::ACTIVE;
    }
}
