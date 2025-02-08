<?php

namespace Company\Domain\Model\Manager\Sales;

use Company\Domain\Model\Customer;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\Manager\Sales;
use Company\Domain\Model\Manager\Sales\FactFindingAssignment\ClosingRequestByFactFinder;
use Company\Domain\Service\SalesFinderService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\CustomerStatus;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Shared\Domain\Enum\SalesRole;
use Tests\TestBase;

class FactFindingAssignmentTest extends TestBase
{
    protected $sales, $customer;
    protected $factFindingAssignment, $assignment;
    //
    protected $id = 'newId';
    protected $salesFinderService, $managerId = 'managerId', $factFindingAssignmentId = 'factFindingAssignmentId', $customerJourney;
    //
    protected $closingRequestByFactFinder;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = $this->buildMockOfClass(Sales::class);
        $this->customer = $this->buildMockOfClass(Customer::class);
        //
        $this->factFindingAssignment = new TestableFactFindingAssignment($this->sales, $this->customer, 'id');
        $this->assignment = $this->buildMockOfClass(CustomerAssignment::class);
        $this->factFindingAssignment->customerAssignment = $this->assignment;
        //
        $this->salesFinderService = $this->buildMockOfClass(SalesFinderService::class);
        $this->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
        //
        $this->closingRequestByFactFinder = $this->buildMockOfClass(ClosingRequestByFactFinder::class);
        $this->factFindingAssignment->closingRequestByFactFinders = new ArrayCollection();
        $this->factFindingAssignment->closingRequestByFactFinders->add($this->closingRequestByFactFinder);
    }
                
    
    //
    protected function construct()
    {
        return new TestableFactFindingAssignment($this->sales, $this->customer, $this->id);
    }
    public function test_construct_setProperties()
    {
        $assignment = $this->construct();
        $this->assertSame($this->sales, $assignment->sales);
        $this->assertSame($this->customer, $assignment->customer);
        $this->assertSame($this->id, $assignment->id);
        $this->assertEquals(CustomerAssignmentStatus::ACTIVE, $assignment->status);
        $this->assertInstanceOf(CustomerAssignment::class, $assignment->customerAssignment);
    }
    public function test_construct_assertSalesActive()
    {
        $this->sales->expects($this->once())
                ->method('assertActive');
        $this->construct();
    }
    public function test_construct_assertSalesHasFactFinderRole()
    {
        $this->sales->expects($this->once())
                ->method('assertRoleEquals')
                ->with(SalesRole::FACT_FINDER);
        $this->construct();
    }
    public function test_construct_assertCustomerHasNoActiveAssignment()
    {
        $this->customer->expects($this->once())
                ->method('assertHasNoActiveAssignment');
        $this->construct();
    }
    public function test_construct_assertCustomerStatusEqualsFactFindingRequired()
    {
        $this->customer->expects($this->once())
                ->method('assertStatusEquals')
                ->with(CustomerStatus::FACT_FINDING_REQUIRED);
        $this->construct();
    }
    
    //
    protected function cancel()
    {
        $this->closingRequestByFactFinder->expects($this->any())
                ->method('getStatus')
                ->willReturn(ManagementApprovalStatus::WAITING_FOR_APPROVAL);
        $this->factFindingAssignment->cancel();
    }
    public function test_cancel_setAssignmentStatusCancelled()
    {
        $this->cancel();
        $this->assertSame(CustomerAssignmentStatus::CANCELLED, $this->factFindingAssignment->status);
    }
    public function test_cancel_cancelAssignmentActiveSchedules()
    {
        $this->assignment->expects($this->once())
                ->method('cancelAllActiveSchedule');
        $this->cancel();
    }
    public function test_cancel_cancelPendingClosingRequest()
    {
        $this->closingRequestByFactFinder->expects($this->once())
                ->method('cancelBySystem');
        $this->cancel();
    }
    public function test_cancel_ignoreCompletedClosingRequest()
    {
        $this->closingRequestByFactFinder->expects($this->once())
                ->method('getStatus')
                ->willReturn(ManagementApprovalStatus::REJECTED);
        $this->closingRequestByFactFinder->expects($this->never())
                ->method('cancelBySystem');
        $this->cancel();
    }
    
    //
    protected function cancelBySystem()
    {
        $this->closingRequestByFactFinder->expects($this->any())
                ->method('getStatus')
                ->willReturn(ManagementApprovalStatus::WAITING_FOR_APPROVAL);
        $this->factFindingAssignment->cancelBySystem();
    }
    public function test_cancelBySystem_setStatusCancelledBySystem()
    {
        $this->cancelBySystem();
        $this->assertSame(CustomerAssignmentStatus::CANCELLED_BY_SYSTEM, $this->factFindingAssignment->status);
    }
    public function test_cancelBySystem_cancelAssignmentActiveSchedules()
    {
        $this->assignment->expects($this->once())
                ->method('cancelAllActiveSchedule');
        $this->cancelBySystem();
    }
    public function test_cancelBySystem_cancelPendingClosingRequestByFactFinder()
    {
        $this->closingRequestByFactFinder->expects($this->once())
                ->method('cancelBySystem');
        $this->cancelBySystem();
    }
    public function test_cancelBySystem_ignoreCompletedClosingRequestByFactFinder()
    {
        $this->closingRequestByFactFinder->expects($this->once())
                ->method('getStatus')
                ->willReturn(ManagementApprovalStatus::REJECTED);
        $this->closingRequestByFactFinder->expects($this->never())
                ->method('cancelBySystem');
        $this->cancelBySystem();
    }
    
    //
    protected function handoverCustomerToStriker()
    {
        $this->sales->expects($this->any())
                ->method('getManagerId')
                ->willReturn($this->managerId);
        return $this->factFindingAssignment->handoverCustomerToStriker($this->salesFinderService, $this->factFindingAssignmentId, $this->customerJourney);
    }
    public function test_handoverCustomerToFactFinder_returnFactFindingAssignment()
    {
        $this->salesFinderService->expects($this->once())
                ->method('findLeastOccupiedStrikerBelongsToManager')
                ->with($this->managerId)
                ->willReturn($this->buildMockOfClass(Sales::class));
        $this->assertInstanceOf(StrikingAssignment::class, $this->handoverCustomerToStriker());
    }
    public function test_handoverCustomerToFactFinder_noSalesForHandoverFound_returnNull()
    {
        $this->salesFinderService->expects($this->once())
                ->method('findLeastOccupiedStrikerBelongsToManager')
                ->with($this->managerId)
                ->willReturn(null);
        $this->assertNull($this->handoverCustomerToStriker());
    }
    
}

class TestableFactFindingAssignment extends FactFindingAssignment
{
    public Sales $sales;
    public Customer $customer;
    public string $id;
    public CustomerAssignmentStatus $status;
    public CustomerAssignment $customerAssignment;
    public Collection $closingRequestByFactFinders;
}
