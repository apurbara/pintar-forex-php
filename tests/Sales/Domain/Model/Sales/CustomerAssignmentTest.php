<?php

namespace Sales\Domain\Model\Sales;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sales\Domain\DependencyModel\Customer;
use Sales\Domain\DependencyModel\Customer\VerificationReportData;
use Sales\Domain\DependencyModel\CustomerData;
use Sales\Domain\DependencyModel\CustomerJourney;
use Sales\Domain\DependencyModel\CustomerVerification;
use Sales\Domain\DependencyModel\Province\City;
use Sales\Domain\DependencyModel\SalesActivity;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment\ClosingRequest;
use Sales\Domain\Model\Sales\CustomerAssignment\ClosingRequestData;
use Sales\Domain\Model\Sales\CustomerAssignment\CustomerAssignmentJourney;
use Sales\Domain\Model\Sales\CustomerAssignment\RecycleRequest;
use Sales\Domain\Model\Sales\CustomerAssignment\RecycleRequestData;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivityScheduleData;
use Sales\Domain\Service\SalesActivitySchedulerService;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\SalesActivityScheduleStatus;
use Shared\Domain\Event\CustomerAssignedEvent;
use Shared\Domain\ValueObject\HourlyTimeInterval;
use Shared\Domain\ValueObject\HourlyTimeIntervalData;
use Tests\TestBase;

class CustomerAssignmentTest extends TestBase
{
    protected $sales;
    protected $customer;
    protected $customerJourney;
    protected $customerAssignment;
    protected $salesActivitySchedule, $schedule;
    //
    protected $id = 'newId';
    protected $city, $customerData;
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
        
        $this->customerAssignment = new TestableCustomerAssignment($this->sales, $this->customer, $this->customerJourney, 'id');
        $this->customerAssignment->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
        
        $this->salesActivitySchedule = $this->buildMockOfClass(SalesActivitySchedule::class);
        $this->schedule = $this->buildMockOfClass(HourlyTimeInterval::class);
        
        $this->customerAssignment->salesActivitySchedules = new ArrayCollection();
        $this->customerAssignment->salesActivitySchedules->add($this->salesActivitySchedule);
        //
        $this->city = $this->buildMockOfClass(City::class);
        $this->customerData = $this->buildMockOfReadonlyClass(CustomerData::class);
        
        //
        $this->salesActivity = $this->buildMockOfClass(SalesActivity::class);
        $this->customerVerification = $this->buildMockOfClass(CustomerVerification::class);
        $this->verificationReportData = new VerificationReportData('note');
        
        $this->closingRequest = $this->buildMockOfClass(ClosingRequest::class);
        $this->customerAssignment->closingRequests = new ArrayCollection();
        $this->customerAssignment->closingRequests->add($this->closingRequest);
        $this->closingRequestData = (new ClosingRequestData(50000000, 'new note'))->setId('closingRequestId');
        
        $this->recycleRequest = $this->buildMockOfClass(RecycleRequest::class);
        $this->customerAssignment->recycleRequests = new ArrayCollection();
        $this->customerAssignment->recycleRequests->add($this->recycleRequest);
        $this->recycleRequestData = (new RecycleRequestData('new note'))->setId('recycleRequestId');
        //
        $this->schedulerService = $this->buildMockOfClass(SalesActivitySchedulerService::class);
    }
    
    //
    protected function construct()
    {
        return new TestableCustomerAssignment($this->sales, $this->customer, $this->customerJourney, $this->id);
    }
    public function test_construct_setProperties()
    {
        $customerAssignment = $this->construct();
        $this->assertSame($this->sales, $customerAssignment->sales);
        $this->assertSame($this->customer, $customerAssignment->customer);
        $this->assertSame($this->customerJourney, $customerAssignment->customerJourney);
        $this->assertSame($this->id, $customerAssignment->id);
        $this->assertEquals(CustomerAssignmentStatus::ACTIVE, $customerAssignment->status);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($customerAssignment->createdTime);
    }
    public function test_construct_storeCustomerAssignedEvent()
    {
        $customerAssignment = $this->construct();
        $event = new CustomerAssignedEvent($this->id);
        $this->assertEquals($event, $customerAssignment->recordedEvents[0]);
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
    public function test_construct_assertSalesActive()
    {
        $this->sales->expects($this->once())
                ->method('assertActive');
        $this->construct();
    }
    public function test_construct_addJourney()
    {
        $this->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
        $customerAssignment = $this->construct();
        $this->assertEquals(1, $customerAssignment->customerAssignmentJourneys->count());
        $this->assertInstanceOf(CustomerAssignmentJourney::class, $customerAssignment->customerAssignmentJourneys->first());
    }
    
    //
    protected function updateCustomer()
    {
        $this->customerAssignment->updateCustomer($this->city, $this->customerData);
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
        $this->customerAssignment->status = CustomerAssignmentStatus::RECYCLED;
        $this->assertRegularExceptionThrowed(fn() => $this->updateCustomer(), 'Forbidden', 'inactive customer assignment');
    }
    
    //
    protected function updateJourney()
    {
        $this->customerAssignment->updateJourney($this->customerJourney);
    }
    public function test_updateJourney_updateJourney()
    {
        $this->updateJourney();
        $this->assertSame($this->customerJourney, $this->customerAssignment->customerJourney);
    }
    public function test_updateJourney_assertNewJourneyActive()
    {
        $this->customerJourney->expects($this->once())
                ->method('assertActive');
        $this->updateJourney();
    }
    public function test_updateJourney_addCustomerAssignmentJourney()
    {
        $this->assertEquals(1, $this->customerAssignment->customerAssignmentJourneys->count());
        $this->updateJourney();
        $this->assertEquals(2, $this->customerAssignment->customerAssignmentJourneys->count());
        $this->assertInstanceOf(CustomerAssignmentJourney::class, $this->customerAssignment->customerAssignmentJourneys->last());
    }
    
    //
    protected function assertBelongsToSales()
    {
        $this->customerAssignment->assertBelongsToSales($this->sales);
    }
    public function test_assertBelongsToSales_differentSales_forbidden()
    {
        $this->customerAssignment->sales = $this->buildMockOfClass(Sales::class);
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
        return $this->customerAssignment->submitSalesActivitySchedule($this->salesActivity, $scheduledSalesActivityData);
    }
    public function test_submitSalesActivitySchedule_returnScheduledSalesActivity()
    {
        $this->assertInstanceOf(SalesActivitySchedule::class, $this->submitSalesActivitySchedule());
    }
    public function test_submitSalesActivitySchedule_inactiveAssignment_forbidden()
    {
        $this->customerAssignment->status = CustomerAssignmentStatus::GOOD_FUND;
        $this->assertRegularExceptionThrowed(fn() => $this->submitSalesActivitySchedule(), 'Forbidden', 'inactive customer assignment');
    }
    
    //
    protected function submitCustomerVerificationReport()
    {
        $this->customerAssignment->SubmitCustomerVerificationReport($this->customerVerification, $this->verificationReportData);
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
        $this->customerAssignment->status = CustomerAssignmentStatus::GOOD_FUND;
        $this->assertRegularExceptionThrowed(fn() => $this->submitCustomerVerificationReport(), 'Forbidden', 'inactive customer assignment');
    }
    
    //
    protected function submitClosingRequest()
    {
        return $this->customerAssignment->submitClosingRequest($this->closingRequestData);
    }
    public function test_submitClosingRequest_returnClosingRequest()
    {
        $this->assertInstanceOf(ClosingRequest::class, $this->submitClosingRequest());
    }
    public function test_submitClosingRequest_inactiveAssignment_forbidden()
    {
        $this->customerAssignment->status = CustomerAssignmentStatus::GOOD_FUND;
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
        return $this->customerAssignment->submitRecycleRequest($this->recycleRequestData);
    }
    public function test_submitRecycleRequest_returnRecycleRequest()
    {
        $this->assertInstanceOf(RecycleRequest::class, $this->submitRecycleRequest());
    }
    public function test_submitRecycleRequest_inactiveAssignment_forbidden()
    {
        $this->customerAssignment->status = CustomerAssignmentStatus::GOOD_FUND;
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
        $this->customerAssignment->addUpcomingScheduleToSchedulerService($this->schedulerService);
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
    
    //
    protected function initiateSalesActivitySchedule()
    {
        $this->customerAssignment->initiateSalesActivitySchedule($this->salesActivity, $this->schedulerService);
    }
    public function test_initiateSalesActivitySchedule_addScheduleToCollection()
    {
        $this->schedulerService->expects($this->once())
                ->method('nextAvailableTimeSlotForScheduleWithDuration');
        $this->initiateSalesActivitySchedule();
        $this->assertEquals(2, $this->customerAssignment->salesActivitySchedules->count());
        $this->assertInstanceOf(SalesActivitySchedule::class, $this->customerAssignment->salesActivitySchedules->last());
    }
    public function test_initiateSalesActivitySchedule_salesResiteringAllUpcomingSchedule()
    {
        $this->sales->expects($this->once())
                ->method('registerAllUpcomingScheduleToScheduler')
                ->with($this->schedulerService);
        $this->initiateSalesActivitySchedule();
    }
}

class TestableCustomerAssignment extends CustomerAssignment
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
    public Collection $customerAssignmentJourneys;
}
