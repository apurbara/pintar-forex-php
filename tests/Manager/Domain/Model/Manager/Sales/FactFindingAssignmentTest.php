<?php

namespace Manager\Domain\Model\Manager\Sales;

use Manager\Domain\DependencyModel\Customer;
use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales;
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
    //
    protected $manager;
    
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
        $this->manager = $this->buildMockOfClass(Manager::class);
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
    protected function closeAssignment()
    {
        $this->factFindingAssignment->closeAssignment();
    }
    public function test_closeAssignment_setAssignmentCompleted()
    {
        $this->closeAssignment();
        $this->assertEquals(CustomerAssignmentStatus::COMPLETED, $this->factFindingAssignment->status);
    }
    public function test_closeAssignment_completeAssignment()
    {
        $this->assignment->expects($this->once())
                ->method('completeAssignment');
        $this->closeAssignment();
    }
    public function test_closeAssignment_updateCustomerStatus()
    {
        $this->customer->expects($this->once())
                ->method('updateStatus')
                ->with(CustomerStatus::TRANSACTION_BY_FACT_FINDER);
        $this->closeAssignment();
    }
    
    //
    protected function belongsToManager()
    {
        return $this->factFindingAssignment->belongsToManager($this->manager);
    }
    public function test_belongsToManager_returnSalesComparisonResult()
    {
        $this->sales->expects($this->once())
                ->method('belongsToManager')
                ->with($this->manager)
                ->willReturn(true);
        $this->assertTrue($this->belongsToManager());
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
