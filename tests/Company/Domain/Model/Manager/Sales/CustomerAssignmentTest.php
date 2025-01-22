<?php

namespace Company\Domain\Model\Manager\Sales;

use Company\Domain\Model\Customer;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\Manager\Sales;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\ClosingRequest;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\CustomerAssignmentJourney;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\RecycleRequest;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\SalesActivitySchedule;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Shared\Domain\Enum\SalesActivityScheduleStatus;
use Tests\TestBase;

class CustomerAssignmentTest extends TestBase
{
    protected $sales;
    protected $customer, $customerId = 'customerId';
    protected $customerJourney;
    protected $customerAssignment;
    //
    protected $id = 'newId';
    //
    protected $closingRequest;
    protected $recycleRequest;
    protected $salesActivitySchedule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = $this->buildMockOfClass(Sales::class);
        $this->customer = $this->buildMockOfClass(Customer::class);
        $this->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
        $this->customerAssignment = new TestableCustomerAssignment($this->sales, $this->customer, $this->customerJourney, 'id');
        
        $this->closingRequest = $this->buildMockOfClass(ClosingRequest::class);
        $this->recycleRequest = $this->buildMockOfClass(RecycleRequest::class);
        $this->salesActivitySchedule = $this->buildMockOfClass(SalesActivitySchedule::class);
        
        $this->customerAssignment->closingRequests = new ArrayCollection();
        $this->customerAssignment->recycleRequests = new ArrayCollection();
        $this->customerAssignment->salesActivitySchedules = new ArrayCollection();
        
        $this->customerAssignment->closingRequests->add($this->closingRequest);
        $this->customerAssignment->recycleRequests->add($this->recycleRequest);
        $this->customerAssignment->salesActivitySchedules->add($this->salesActivitySchedule);
    }
    
    //
    protected function construct()
    {
        return new TestableCustomerAssignment($this->sales, $this->customer, $this->customerJourney, $this->id);
    }
    public function test_construct_setProperties()
    {
        $assignment = $this->construct();
        $this->assertSame($this->sales, $assignment->sales);
        $this->assertSame($this->customer, $assignment->customer);
        $this->assertSame($this->customerJourney, $assignment->customerJourney);
        $this->assertSame($this->id, $assignment->id);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($assignment->createdTime);
        $this->assertSame(CustomerAssignmentStatus::ACTIVE, $assignment->status);
    }
    public function test_construct_assertCustomerJourneyActive()
    {
        $this->customerJourney->expects($this->once())
                ->method('assertActive');
        $this->construct();
    }
    public function test_construct_assertCustomerHasNoActiveAssignment()
    {
        $this->customer->expects($this->once())
                ->method('assertHasNoActiveAssignment');
        $this->construct();
    }
    public function test_construct_assertSalesActive()
    {
        $this->sales->expects($this->once())
                ->method('assertActive');
        $this->construct();
    }
    public function test_construct_noInitialJourney()
    {
        $this->customerJourney = null;
        $this->construct();
        $this->markAsSuccess();
    }
    public function test_construct_addCustomerAssignmentJourney()
    {
        $assignment = $this->construct();
        $this->assertEquals(1, $assignment->customerAssignmentJourneys->count());
        $this->assertInstanceOf(CustomerAssignmentJourney::class, $assignment->customerAssignmentJourneys->first());
    }
    
    //
    protected function cancel()
    {
        $this->closingRequest->expects($this->any())
                ->method('getStatus')
                ->willReturn(ManagementApprovalStatus::REJECTED);
        $this->recycleRequest->expects($this->any())
                ->method('getStatus')
                ->willReturn(ManagementApprovalStatus::REJECTED);
        $this->salesActivitySchedule->expects($this->any())
                ->method('getStatus')
                ->willReturn(SalesActivityScheduleStatus::COMPLETED);
        $this->customerAssignment->cancel();
    }
    public function test_cancel_setStatusCancelled()
    {
        $this->cancel();
        $this->assertSame(CustomerAssignmentStatus::CANCELLED, $this->customerAssignment->status);
    }
    public function test_cancel_hasPedingClosingRequest_forbidden()
    {
        $this->closingRequest->expects($this->any())
                ->method('getStatus')
                ->willReturn(ManagementApprovalStatus::WAITING_FOR_APPROVAL);
        $this->assertRegularExceptionThrowed(fn() => $this->cancel(), 'Forbidden', 'customer assignment has pending request or schedule');
    }
    public function test_cancel_hasPedingRecycleRequest_forbidden()
    {
        $this->recycleRequest->expects($this->any())
                ->method('getStatus')
                ->willReturn(ManagementApprovalStatus::WAITING_FOR_APPROVAL);
        $this->assertRegularExceptionThrowed(fn() => $this->cancel(), 'Forbidden', 'customer assignment has pending request or schedule');
    }
    public function test_cancel_hasScheduledActivity_forbidden()
    {
        $this->salesActivitySchedule->expects($this->any())
                ->method('getStatus')
                ->willReturn(SalesActivityScheduleStatus::SCHEDULED);
        $this->assertRegularExceptionThrowed(fn() => $this->cancel(), 'Forbidden', 'customer assignment has pending request or schedule');
    }
    
    //
    protected function cancelBySystem()
    {
        $this->closingRequest->expects($this->any())
                ->method('getStatus')
                ->willReturn(ManagementApprovalStatus::WAITING_FOR_APPROVAL);
        $this->recycleRequest->expects($this->any())
                ->method('getStatus')
                ->willReturn(ManagementApprovalStatus::WAITING_FOR_APPROVAL);
        $this->salesActivitySchedule->expects($this->any())
                ->method('getStatus')
                ->willReturn(SalesActivityScheduleStatus::SCHEDULED);
        $this->customerAssignment->cancelBySystem();
    }
    public function test_cancelBySystem_setStatusCancelled()
    {
        $this->cancelBySystem();
        $this->assertEquals(CustomerAssignmentStatus::CANCELLED_BY_SYSTEM, $this->customerAssignment->status);
    }
    public function test_cancelBySystem_cancelPendingClosingRequest()
    {
        $this->closingRequest->expects($this->once())
                ->method('cancelBySystem');
        $this->cancelBySystem();
    }
    public function test_cancelBySystem_ignoredConcludedClosingRequest()
    {
        $this->closingRequest->expects($this->once())
                ->method('getStatus')
                ->willReturn(ManagementApprovalStatus::APPROVED);
        $this->closingRequest->expects($this->never())
                ->method('cancelBySystem');
        $this->cancelBySystem();
    }
    public function test_cancelBySystem_cancelPendingRecycleRequest()
    {
        $this->recycleRequest->expects($this->once())
                ->method('cancelBySystem');
        $this->cancelBySystem();
    }
    public function test_cancelBySystem_ignoredConcludedRecycleRequest()
    {
        $this->recycleRequest->expects($this->once())
                ->method('getStatus')
                ->willReturn(ManagementApprovalStatus::APPROVED);
        $this->recycleRequest->expects($this->never())
                ->method('cancelBySystem');
        $this->cancelBySystem();
    }
    public function test_cancelBySystem_cancelScheduledActivity()
    {
        $this->salesActivitySchedule->expects($this->once())
                ->method('cancelBySystem');
        $this->cancelBySystem();
    }
    public function test_cancelBySystem_ignoredConcludedActivity()
    {
        $this->salesActivitySchedule->expects($this->once())
                ->method('getStatus')
                ->willReturn(SalesActivityScheduleStatus::COMPLETED);
        $this->salesActivitySchedule->expects($this->never())
                ->method('cancelBySystem');
        $this->cancelBySystem();
    }
}

class TestableCustomerAssignment extends CustomerAssignment
{
    public Sales $sales;
    public Customer $customer;
    public ?CustomerJourney $customerJourney;
    public string $id;
    public DateTimeImmutable $createdTime;
    public CustomerAssignmentStatus $status = CustomerAssignmentStatus::ACTIVE;
    public Collection $closingRequests;
    public Collection $recycleRequests;
    public Collection $salesActivitySchedules;
    public Collection $customerAssignmentJourneys;
}
