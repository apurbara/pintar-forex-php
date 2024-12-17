<?php

namespace Company\Domain\Model\Manager\Sales;

use Company\Domain\Model\Customer;
use Company\Domain\Model\Manager\Sales;
use Company\Domain\Service\SalesFinderService;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\CustomerStatus;
use Shared\Domain\Enum\SalesRole;
use Tests\TestBase;

class GreetingAssignmentTest extends TestBase
{
    protected $sales, $customer;
    protected $greetingAssignment, $assignment;
    //
    protected $id = 'newId';
    //
    protected $salesFinderService, $managerId = 'managerId', $factFindingAssignmentId = 'factFindingAssignmentId';

    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = $this->buildMockOfClass(Sales::class);
        $this->customer = $this->buildMockOfClass(Customer::class);
        //
        $this->greetingAssignment = new TestableGreetingAssignment($this->sales, $this->customer, 'id');
        $this->assignment = $this->buildMockOfClass(CustomerAssignment::class);
        $this->greetingAssignment->customerAssignment = $this->assignment;
        //
        $this->salesFinderService = $this->buildMockOfClass(SalesFinderService::class);
    }
    
    //
    protected function construct()
    {
        return new TestableGreetingAssignment($this->sales, $this->customer, $this->id);
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
    public function test_construct_assertSalesHasGreeterRole()
    {
        $this->sales->expects($this->once())
                ->method('assertRoleEquals')
                ->with(SalesRole::GREETER);
        $this->construct();
    }
    public function test_construct_assertCustomerHasNoActiveAssignment()
    {
        $this->customer->expects($this->once())
                ->method('assertHasNoActiveAssignment');
        $this->construct();
    }
    public function test_construct_assertCustomerStatusWithinNewOnRecycleStatus()
    {
        $this->customer->expects($this->once())
                ->method('assertStatusIn')
                ->with([CustomerStatus::NEW, CustomerStatus::RECYCLED]);
        $this->construct();
    }
    
    //
    protected function cancel()
    {
        $this->greetingAssignment->cancel();
    }
    public function test_cancel_setAssignmentStatusCancelled()
    {
        $this->cancel();
        $this->assertSame(CustomerAssignmentStatus::CANCELLED, $this->greetingAssignment->status);
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
        $this->greetingAssignment->cancelBySystem();
    }
    public function test_cancelBySystem_setStatusCancelledBySystem()
    {
        $this->cancelBySystem();
        $this->assertSame(CustomerAssignmentStatus::CANCELLED_BY_SYSTEM, $this->greetingAssignment->status);
    }
    public function test_cancelBySystem_cancelAssignmentActiveSchedules()
    {
        $this->assignment->expects($this->once())
                ->method('cancelAllActiveSchedule');
        $this->cancelBySystem();
    }
    
    //
    protected function handoverCustomerToFactFinder()
    {
        $this->sales->expects($this->any())
                ->method('getManagerId')
                ->willReturn($this->managerId);
        return $this->greetingAssignment->handoverCustomerToFactFinder($this->salesFinderService, $this->factFindingAssignmentId);
    }
    public function test_handoverCustomerToFactFinder_returnFactFindingAssignment()
    {
        $this->salesFinderService->expects($this->once())
                ->method('findLeastOccupiedFactFinderBelongsToManager')
                ->with($this->managerId)
                ->willReturn($this->buildMockOfClass(Sales::class));
        $this->assertInstanceOf(FactFindingAssignment::class, $this->handoverCustomerToFactFinder());
    }
    public function test_handoverCustomerToFactFinder_noSalesForHandoverFound_returnNull()
    {
        $this->salesFinderService->expects($this->once())
                ->method('findLeastOccupiedFactFinderBelongsToManager')
                ->with($this->managerId)
                ->willReturn(null);
        $this->assertNull($this->handoverCustomerToFactFinder());
    }
    
}

class TestableGreetingAssignment extends GreetingAssignment
{
    public Sales $sales;
    public Customer $customer;
    public string $id;
    public CustomerAssignmentStatus $status;
    public CustomerAssignment $customerAssignment;
}
