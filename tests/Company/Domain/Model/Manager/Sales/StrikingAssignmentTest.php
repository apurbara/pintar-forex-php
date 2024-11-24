<?php

namespace Company\Domain\Model\Manager\Sales;

use Company\Domain\Model\Customer;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\Manager\Sales;
use Company\Domain\Model\Manager\Sales\StrikingAssignment\ClosingRequest;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\CustomerStatus;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Shared\Domain\Enum\SalesRole;
use Tests\TestBase;

class StrikingAssignmentTest extends TestBase
{
    protected $sales, $customer, $customerJourney;
    protected $strikingAssignment, $assignment;
    //
    protected $id = 'newId';
    //
    protected $closingRequest;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = $this->buildMockOfClass(Sales::class);
        $this->customer = $this->buildMockOfClass(Customer::class);
        $this->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
        //
        $this->strikingAssignment = new TestableStrikingAssignment($this->sales, $this->customer, 'id', $this->customerJourney);
        $this->assignment = $this->buildMockOfClass(CustomerAssignment::class);
        $this->strikingAssignment->customerAssignment = $this->assignment;
        //
        $this->closingRequest = $this->buildMockOfClass(ClosingRequest::class);
        $this->strikingAssignment->closingRequests = new ArrayCollection();
        $this->strikingAssignment->closingRequests->add($this->closingRequest);
    }
    
    //
    protected function construct()
    {
        return new TestableStrikingAssignment($this->sales, $this->customer, $this->id, $this->customerJourney);
    }
    public function test_construct_setProperties()
    {
        $assignment = $this->construct();
        $this->assertSame($this->sales, $assignment->sales);
        $this->assertSame($this->customer, $assignment->customer);
        $this->assertSame($this->id, $assignment->id);
        $this->assertSame($this->customerJourney, $assignment->customerJourney);
        $this->assertEquals(CustomerAssignmentStatus::ACTIVE, $assignment->status);
        $this->assertInstanceOf(CustomerAssignment::class, $assignment->customerAssignment);
    }
    public function test_construct_assertSalesActive()
    {
        $this->sales->expects($this->once())
                ->method('assertActive');
        $this->construct();
    }
    public function test_construct_assertSalesHasStrikerRole()
    {
        $this->sales->expects($this->once())
                ->method('assertRoleEquals')
                ->with(SalesRole::STRIKER);
        $this->construct();
    }
    public function test_construct_assertCustomerHasNoActiveAssignment()
    {
        $this->customer->expects($this->once())
                ->method('assertHasNoActiveAssignment');
        $this->construct();
    }
    public function test_construct_assertCustomerStatusEqualsStrikingRequired()
    {
        $this->customer->expects($this->once())
                ->method('assertStatusEquals')
                ->with(CustomerStatus::STRIKING_REQUIRED);
        $this->construct();
    }
    public function test_construct_assertCustomerJourneyActive()
    {
        $this->customerJourney->expects($this->once())
                ->method('assertActive');
        $this->construct();
    }
    public function test_construct_emptyCustomerJourney()
    {
        $this->customerJourney = null;
        $this->construct();
        $this->markAsSuccess();
    }
    
    //
    protected function cancel()
    {
        $this->closingRequest->expects($this->any())
                ->method('getStatus')
                ->willReturn(ManagementApprovalStatus::WAITING_FOR_APPROVAL);
        $this->strikingAssignment->cancel();
    }
    public function test_cancel_setAssignmentStatusCancelled()
    {
        $this->cancel();
        $this->assertSame(CustomerAssignmentStatus::CANCELLED, $this->strikingAssignment->status);
    }
    public function test_cancel_cancelAssignmentActiveSchedules()
    {
        $this->assignment->expects($this->once())
                ->method('cancelAllActiveSchedule');
        $this->cancel();
    }
    public function test_cancel_cancelPendingClosingRequest()
    {
        $this->closingRequest->expects($this->once())
                ->method('cancelBySystem');
        $this->cancel();
    }
    public function test_cancel_ignoreCompletedClosingRequest()
    {
        $this->closingRequest->expects($this->once())
                ->method('getStatus')
                ->willReturn(ManagementApprovalStatus::REJECTED);
        $this->closingRequest->expects($this->never())
                ->method('cancelBySystem');
        $this->cancel();
    }
    
    //
    protected function cancelBySystem()
    {
        $this->closingRequest->expects($this->any())
                ->method('getStatus')
                ->willReturn(ManagementApprovalStatus::WAITING_FOR_APPROVAL);
        $this->strikingAssignment->cancelBySystem();
    }
    public function test_cancelBySystem_setStatusCancelledBySystem()
    {
        $this->cancelBySystem();
        $this->assertSame(CustomerAssignmentStatus::CANCELLED_BY_SYSTEM, $this->strikingAssignment->status);
    }
    public function test_cancelBySystem_cancelAssignmentActiveSchedules()
    {
        $this->assignment->expects($this->once())
                ->method('cancelAllActiveSchedule');
        $this->cancelBySystem();
    }
    public function test_cancelBySystem_cancelPendingClosingRequest()
    {
        $this->closingRequest->expects($this->once())
                ->method('cancelBySystem');
        $this->cancelBySystem();
    }
    public function test_cancelBySystem_ignoreCompletedClosingRequest()
    {
        $this->closingRequest->expects($this->once())
                ->method('getStatus')
                ->willReturn(ManagementApprovalStatus::REJECTED);
        $this->closingRequest->expects($this->never())
                ->method('cancelBySystem');
        $this->cancelBySystem();
    }
    
}

class TestableStrikingAssignment extends StrikingAssignment
{
    public Sales $sales;
    public Customer $customer;
    public string $id;
    public CustomerAssignmentStatus $status;
    public CustomerAssignment $customerAssignment;
    public Collection $closingRequests;
    public ?CustomerJourney $customerJourney;
}
