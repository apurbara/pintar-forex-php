<?php

namespace Sales\Domain\Model\Sales;

use Sales\Domain\DependencyModel\Customer;
use Sales\Domain\DependencyModel\CustomerData;
use Sales\Domain\DependencyModel\Province\City;
use Sales\Domain\DependencyModel\SalesActivity;
use Sales\Domain\Event\CustomerValidated;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReportData;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivityScheduleData;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\GreetingResult;
use Shared\Domain\ValueObject\HourlyTimeIntervalData;
use Tests\TestBase;

class GreetingAssignmentTest extends TestBase
{

    protected $sales, $customer, $customerAssignment;
    protected $greetingAssignment;
    //
    protected $customerData, $city;
    protected $salesActivity;
    protected $reportId = 'reportId', $salesActivityReportData;
    protected $scheduleId = 'scheduleId', $salesActivityScheduleData;
    protected $rating = 3;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = $this->buildMockOfClass(Sales::class);
        $this->customer = $this->buildMockOfClass(Customer::class);
        $this->customerAssignment = $this->buildMockOfClass(CustomerAssignment::class);

        $this->greetingAssignment = new TestableGreetingAssignment();
        $this->greetingAssignment->sales = $this->sales;
        $this->greetingAssignment->customer = $this->customer;
        $this->greetingAssignment->customerAssignment = $this->customerAssignment;
        //
        $this->customerData = new CustomerData();
        $this->city = $this->buildMockOfClass(City::class);
        
        $this->salesActivity = $this->buildMockOfClass(SalesActivity::class);
        $this->salesActivityReportData = new SalesActivityReportData('content');
        $this->salesActivityScheduleData = new SalesActivityScheduleData(new HourlyTimeIntervalData('new week'));
    }
    
    //
    protected function isBelongsToSales()
    {
        return $this->greetingAssignment->isBelongsToSales($this->sales);
    }
    public function test_isBelongsToSales_sameSales_returnTrue()
    {
        $this->assertTrue($this->isBelongsToSales());
    }
    public function test_isBelongsToSales_diffSales_returnFalse()
    {
        $this->greetingAssignment->sales = $this->buildMockOfClass(Sales::class);
        $this->assertFalse($this->isBelongsToSales());
    }

    //
    protected function assertBelongsToSales()
    {
        $this->greetingAssignment->assertBelongsToSales($this->sales);
    }
    public function test_assertBelongsToSales_differentSales_forbidden()
    {
        $this->greetingAssignment->sales = $this->buildMockOfClass(Sales::class);
        $this->assertRegularExceptionThrowed(fn() => $this->assertBelongsToSales(), 'Forbidden', 'unmanaged assignment');
    }
    public function test_assertBelongsToSales_sameSales_void()
    {
        $this->assertBelongsToSales();
        $this->markAsSuccess();
    }
    
    //
    protected function updateCustomer()
    {
        $this->greetingAssignment->updateCustomer($this->customerData, $this->city);
    }
    public function test_updateCustomer_updateCustomer()
    {
        $this->customer->expects($this->once())
                ->method('update')
                ->with($this->city, $this->customerData);
        $this->updateCustomer();
    }
    public function test_updateCustomer_inactiveAssignment_forbidden()
    {
        $this->greetingAssignment->status = CustomerAssignmentStatus::COMPLETED;
        $this->assertRegularExceptionThrowed(fn() => $this->updateCustomer(), 'Forbidden', 'inactive assignment');
    }
    
    //
    protected function updateCustomerRating()
    {
        $this->greetingAssignment->updateCustomerRating($this->rating);
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
        $this->greetingAssignment->status = CustomerAssignmentStatus::COMPLETED;
        $this->assertRegularExceptionThrowed(fn() => $this->updateCustomerRating(), 'Forbidden', 'inactive assignment');
    }
    
    //
    protected function markCustomerValid()
    {
        $this->greetingAssignment->validateCustomer();
    }
    public function test_markCustomerValid_markCustomerAsInvalid()
    {
        $this->customer->expects($this->once())
                ->method('validate');
        $this->markCustomerValid();
    }
    public function test_markCustomerValid_inactiveAssignment_forbidden()
    {
        $this->greetingAssignment->status = CustomerAssignmentStatus::COMPLETED;
        $this->assertRegularExceptionThrowed(fn() => $this->markCustomerValid(), 'Forbidden', 'inactive assignment');
    }
    public function test_markCustomerValid_recordCustomerValidatedEvent()
    {
        $event = new CustomerValidated($this->greetingAssignment->id);
        $this->markCustomerValid();
        $this->assertEquals($event, $this->greetingAssignment->recordedEvents[0]);
    }
    public function test_markCustomerValid_setAssignmentCompleted()
    {
        $this->markCustomerValid();
        $this->assertEquals(CustomerAssignmentStatus::COMPLETED, $this->greetingAssignment->status);
    }
    
    //
    protected function recycleCustomer()
    {
        $this->greetingAssignment->recycleCustomer();
    }
    public function test_recycleCustomer_markCustomerAsInvalid()
    {
        $this->customer->expects($this->once())
                ->method('recycle');
        $this->recycleCustomer();
    }
    public function test_recycleCustomer_inactiveAssignment_forbidden()
    {
        $this->greetingAssignment->status = CustomerAssignmentStatus::COMPLETED;
        $this->assertRegularExceptionThrowed(fn() => $this->recycleCustomer(), 'Forbidden', 'inactive assignment');
    }
    public function test_recycleCustomer_setAssignmentRecycled()
    {
        $this->recycleCustomer();
        $this->assertEquals(CustomerAssignmentStatus::RECYCLED, $this->greetingAssignment->status);
    }

    //
    protected function submitNonScheduledSalesActivityReport()
    {
        return $this->greetingAssignment->submitNonScheduledSalesActivityReport($this->salesActivity, $this->reportId,
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
        $this->greetingAssignment->status = CustomerAssignmentStatus::COMPLETED;
        $this->assertRegularExceptionThrowed(fn() => $this->submitNonScheduledSalesActivityReport(), 'Forbidden',
                'inactive assignment');
    }

    //
    protected function submitSalesActivitySchedule()
    {
        $hourlyTimeIntervalData = new HourlyTimeIntervalData('next week');
        $scheduledSalesActivityData = (new SalesActivityScheduleData($hourlyTimeIntervalData))->setId('scheduleId');
        return $this->greetingAssignment->submitSalesActivitySchedule($this->salesActivity, $this->scheduleId,
                        $scheduledSalesActivityData);
    }
    public function test_submitSalesActivitySchedule_returnScheduledSalesActivity()
    {
        $this->assertInstanceOf(SalesActivitySchedule::class, $this->submitSalesActivitySchedule());
    }
    public function test_submitSalesActivitySchedule_inactiveAssignment_forbidden()
    {
        $this->greetingAssignment->status = CustomerAssignmentStatus::COMPLETED;
        $this->assertRegularExceptionThrowed(fn() => $this->submitSalesActivitySchedule(), 'Forbidden',
                'inactive assignment');
    }
}

class TestableGreetingAssignment extends GreetingAssignment
{

    public Sales $sales;
    public Customer $customer;
    public string $id = 'greetingAssignmentId';
    public CustomerAssignmentStatus $status;
    public CustomerAssignment $customerAssignment;
    public $recordedEvents;

    public function __construct()
    {
        parent::__construct();
        $this->status = CustomerAssignmentStatus::ACTIVE;
    }
}
