<?php

namespace Company\Domain\Model\Manager\Sales;

use Company\Domain\Model\Customer;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\Manager\Sales;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\ClosingRequest;
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
    protected $customerAssignment;
    //
    protected $closingRequest;
    protected $recycleRequest;
    protected $salesActivitySchedule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customerAssignment = new TestableCustomerAssignment();
        
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
    
    function __construct()
    {
        parent::__construct();
    }
}
