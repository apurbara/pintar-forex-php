<?php

namespace Sales\Domain\Model\Personnel\Sales;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\CustomerJourney;
use Sales\Domain\Model\CustomerVerification;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\ClosingRequest;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\ClosingRequestData;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\RecycleRequest;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\RecycleRequestData;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivityScheduleData;
use Sales\Domain\Model\SalesActivity;
use Sales\Domain\Service\SalesActivitySchedulerService;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use SharedContext\Domain\Enum\SalesActivityScheduleStatus;
use SharedContext\Domain\Event\CustomerAssignedEvent;
use SharedContext\Domain\ValueObject\HourlyTimeInterval;
use SharedContext\Domain\ValueObject\HourlyTimeIntervalData;
use Tests\TestBase;

class AssignedCustomerTest extends TestBase
{
    protected $sales;
    protected $customer;
    protected $customerJourney;
    protected $assignedCustomer;
    protected $salesActivitySchedule, $schedule;
    //
    protected $id = 'newId', $customerData;
    //
    protected $salesActivity;
    protected $customerVerification, $verificationReportData;
    protected $closingRequest, $closingRequestData;
    protected $recycleRequest, $recycleRequestData;
    //
    protected $schedulerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = $this->buildMockOfClass(Sales::class);
        $this->customer = $this->buildMockOfClass(Customer::class);
        $this->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
        
        $this->assignedCustomer = new TestableAssignedCustomer($this->sales, $this->customer, $this->customerJourney, 'id');
        $this->assignedCustomer->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
        
        $this->salesActivitySchedule = $this->buildMockOfClass(SalesActivitySchedule::class);
        $this->schedule = $this->buildMockOfClass(HourlyTimeInterval::class);
        
        $this->assignedCustomer->salesActivitySchedules = new ArrayCollection();
        $this->assignedCustomer->salesActivitySchedules->add($this->salesActivitySchedule);
        
        //
        $this->salesActivity = $this->buildMockOfClass(SalesActivity::class);
        $this->customerVerification = $this->buildMockOfClass(CustomerVerification::class);
        $this->verificationReportData = new Customer\VerificationReportData('note');
        
        $this->closingRequest = $this->buildMockOfClass(ClosingRequest::class);
        $this->assignedCustomer->closingRequests = new ArrayCollection();
        $this->assignedCustomer->closingRequests->add($this->closingRequest);
        $this->closingRequestData = (new ClosingRequestData(50000000, 'new note'))->setId('closingRequestId');
        
        $this->recycleRequest = $this->buildMockOfClass(RecycleRequest::class);
        $this->assignedCustomer->recycleRequests = new ArrayCollection();
        $this->assignedCustomer->recycleRequests->add($this->recycleRequest);
        $this->recycleRequestData = (new RecycleRequestData('new note'))->setId('recycleRequestId');
        //
        $this->schedulerService = $this->buildMockOfClass(SalesActivitySchedulerService::class);
    }
    
    //
    protected function construct()
    {
        return new TestableAssignedCustomer($this->sales, $this->customer, $this->customerJourney, $this->id);
    }
    public function test_construct_setProperties()
    {
        $assignedCustomer = $this->construct();
        $this->assertSame($this->sales, $assignedCustomer->sales);
        $this->assertSame($this->customer, $assignedCustomer->customer);
        $this->assertSame($this->customerJourney, $assignedCustomer->customerJourney);
        $this->assertSame($this->id, $assignedCustomer->id);
        $this->assertEquals(CustomerAssignmentStatus::ACTIVE, $assignedCustomer->status);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($assignedCustomer->createdTime);
    }
    public function test_construct_storeCustomerAssignedEvent()
    {
        $assignedCustomer = $this->construct();
        $event = new CustomerAssignedEvent($this->id);
        $this->assertEquals($event, $assignedCustomer->recordedEvents[0]);
    }
    public function test_construct_assertCustomerJourneyActive()
    {
        $this->customerJourney->expects($this->once())
                ->method('assertActive');
        $this->construct();
    }
    public function test_construct_noCustomerJourney_void()
    {
        $this->customerJourney = null;
        $this->construct();
        $this->markAsSuccess();
    }
    
    //
    protected function updateJourney()
    {
        $this->assignedCustomer->updateJourney($this->customerJourney);
    }
    public function test_updateJourney_updateJourney()
    {
        $this->updateJourney();
        $this->assertSame($this->customerJourney, $this->assignedCustomer->customerJourney);
    }
    public function test_updateJourney_assertNewJourneyActive()
    {
        $this->customerJourney->expects($this->once())
                ->method('assertActive');
        $this->updateJourney();
    }
    
    //
    protected function assertBelongsToSales()
    {
        $this->assignedCustomer->assertBelongsToSales($this->sales);
    }
    public function test_assertBelongsToSales_differentSales_forbidden()
    {
        $this->assignedCustomer->sales = $this->buildMockOfClass(Sales::class);
        $this->assertRegularExceptionThrowed(fn() => $this->assertBelongsToSales(), 'Forbidden', 'unmanaged assigned customer');
    }
    public function test_assertBelongsToSales_sameSales_void()
    {
        $this->assertBelongsToSales();
        $this->markAsSuccess();
    }
    
    //
    protected function submitSalesActivitySchedule()
    {
        $hourlyTimeIntervalData = new HourlyTimeIntervalData('next week');
        $scheduledSalesActivityData = (new SalesActivityScheduleData($hourlyTimeIntervalData))->setId('scheduleId');
        return $this->assignedCustomer->submitSalesActivitySchedule($this->salesActivity, $scheduledSalesActivityData);
    }
    public function test_submitSalesActivitySchedule_returnScheduledSalesActivity()
    {
        $this->assertInstanceOf(SalesActivitySchedule::class, $this->submitSalesActivitySchedule());
    }
    public function test_submitSalesActivitySchedule_inactiveAssignment_forbidden()
    {
        $this->assignedCustomer->status = CustomerAssignmentStatus::GOOD_FUND;
        $this->assertRegularExceptionThrowed(fn() => $this->submitSalesActivitySchedule(), 'Forbidden', 'inactive customer assignment');
    }
    
    //
    protected function submitVerificationReport()
    {
        $this->assignedCustomer->submitVerificationReport($this->customerVerification, $this->verificationReportData);
    }
    public function test_submitVerificationReport_submitCustomerVerificationReport()
    {
        $this->customer->expects($this->once())
                ->method('submitVerificationReport')
                ->with($this->customerVerification, $this->verificationReportData);
        $this->submitVerificationReport();
    }
    public function test_submitVerificationReport_inactiveAssignment_forbidden()
    {
        $this->assignedCustomer->status = CustomerAssignmentStatus::GOOD_FUND;
        $this->assertRegularExceptionThrowed(fn() => $this->submitVerificationReport(), 'Forbidden', 'inactive customer assignment');
    }
    
    //
    protected function submitClosingRequest()
    {
        return $this->assignedCustomer->submitClosingRequest($this->closingRequestData);
    }
    public function test_submitClosingRequest_returnClosingRequest()
    {
        $this->assertInstanceOf(ClosingRequest::class, $this->submitClosingRequest());
    }
    public function test_submitClosingRequest_inactiveAssignment_forbidden()
    {
        $this->assignedCustomer->status = CustomerAssignmentStatus::GOOD_FUND;
        $this->assertRegularExceptionThrowed(fn() => $this->submitClosingRequest(), 'Forbidden', 'inactive customer assignment');
    }
    public function test_submitClosingRequest_hasUnconcludedClosingRequest()
    {
        $this->closingRequest->expects($this->once())
                ->method('isOngoing')
                ->willReturn(true);
        $this->assertRegularExceptionThrowed(fn() => $this->submitClosingRequest(), 'Forbidden', 'there area still ongoing closing/recycle request on this assignment');
    }
    public function test_submitClosingRequest_hasUnconcludedRecycleRequest()
    {
        $this->recycleRequest->expects($this->once())
                ->method('isOngoing')
                ->willReturn(true);
        $this->assertRegularExceptionThrowed(fn() => $this->submitClosingRequest(), 'Forbidden', 'there area still ongoing closing/recycle request on this assignment');
    }
    
    //
    protected function submitRecycleRequest()
    {
        return $this->assignedCustomer->submitRecycleRequest($this->recycleRequestData);
    }
    public function test_submitRecycleRequest_returnRecycleRequest()
    {
        $this->assertInstanceOf(RecycleRequest::class, $this->submitRecycleRequest());
    }
    public function test_submitRecycleRequest_inactiveAssignment_forbidden()
    {
        $this->assignedCustomer->status = CustomerAssignmentStatus::GOOD_FUND;
        $this->assertRegularExceptionThrowed(fn() => $this->submitRecycleRequest(), 'Forbidden', 'inactive customer assignment');
    }
    public function test_submitRecycleRequest_hasUnconcludedClosingRequest()
    {
        $this->closingRequest->expects($this->once())
                ->method('isOngoing')
                ->willReturn(true);
        $this->assertRegularExceptionThrowed(fn() => $this->submitRecycleRequest(), 'Forbidden', 'there area still ongoing closing/recycle request on this assignment');
    }
    public function test_submitRecycleRequest_hasUnconcludedRecycleRequest()
    {
        $this->recycleRequest->expects($this->once())
                ->method('isOngoing')
                ->willReturn(true);
        $this->assertRegularExceptionThrowed(fn() => $this->submitRecycleRequest(), 'Forbidden', 'there area still ongoing closing/recycle request on this assignment');
    }
    
    //
    protected function addUpcomingScheduleToSchedulerService()
    {
        $this->schedule->expects($this->any())
                ->method('getStartTime')
                ->willReturn(new DateTimeImmutable('tomorrow'));
        //
        $this->salesActivitySchedule->expects($this->any())
                ->method('getStatus')
                ->willReturn(SalesActivityScheduleStatus::SCHEDULED);
        $this->salesActivitySchedule->expects($this->any())
                ->method('getSchedule')
                ->willReturn($this->schedule);
        $this->assignedCustomer->addUpcomingScheduleToSchedulerService($this->schedulerService);
    }
    public function test_addUpcomingScheduleToSchedulerService_includeScheduleInService()
    {
        $this->salesActivitySchedule->expects($this->once())
                ->method('includeInSchedulerService')
                ->with($this->schedulerService);
        $this->addUpcomingScheduleToSchedulerService();
    }
    public function test_addUpcomingScheduleToSchedulerService_excludeNonScheduledSchedule()
    {
        $this->salesActivitySchedule->expects($this->any())
                ->method('getStatus')
                ->willReturn(SalesActivityScheduleStatus::COMPLETED);
        $this->salesActivitySchedule->expects($this->never())
                ->method('includeInSchedulerService')
                ->with($this->schedulerService);
        $this->addUpcomingScheduleToSchedulerService();
    }
    //unfortunately embedded criteria untestable
    public function test_addUpcomingScheduleToSchedulerService_excludeNonUpcomingSchedule()
    {
        $this->schedule->expects($this->once())
                ->method('getStartTime')
                ->willReturn(new DateTimeImmutable('yesterday'));
        $this->salesActivitySchedule->expects($this->never())
                ->method('includeInSchedulerService')
                ->with($this->schedulerService);
        $this->addUpcomingScheduleToSchedulerService();
    }
}

class TestableAssignedCustomer extends AssignedCustomer
{
    public Sales $sales;
    public Customer $customer;
    public ?CustomerJourney $customerJourney;
    public string $id;
    public CustomerAssignmentStatus $status;
    public DateTimeImmutable $createdTime;
    public Collection $closingRequests;
    public Collection $recycleRequests;
    public $recordedEvents = [];
    public Collection $salesActivitySchedules;
}
