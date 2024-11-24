<?php

namespace Company\Domain\Model\Manager\Sales;

use Company\Domain\Model\Customer;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\Manager\Sales;
use Company\Domain\Service\SalesFinderService;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\CustomerStatus;
use Shared\Domain\Enum\SalesRole;
use Tests\TestBase;

class FactFindingAssignmentTest extends TestBase
{
    protected $sales, $customer;
    protected $factFindingAssignment, $assignment;
    //
    protected $id = 'newId';
    protected $salesFinderService, $managerId = 'managerId', $strikingAssignmentId = 'strikingAssignmentId', $customerJourney;
    
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
    
    //
    protected function cancelBySystem()
    {
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
    
    //
    protected function handoverCustomerToStriker()
    {
        $this->sales->expects($this->any())
                ->method('getManagerId')
                ->willReturn($this->managerId);
        return $this->factFindingAssignment->handoverCustomerToStriker($this->salesFinderService, $this->strikingAssignmentId, $this->customerJourney);
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
}
