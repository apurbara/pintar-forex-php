<?php

namespace Manager\Domain\Model\Personnel\Manager\Sales;

use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Model\Personnel\Manager\Sales;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use Tests\TestBase;

class AssignedCustomerTest extends TestBase
{
    protected $assignedCustomer;
    protected $sales;
    //
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assignedCustomer = new TestableAssignedCustomer();
        $this->sales = $this->buildMockOfClass(Sales::class);
        $this->assignedCustomer->sales = $this->sales;
        //
        $this->manager = $this->buildMockOfClass(Manager::class);
    }
    
    //
    protected function closeAssignment()
    {
        $this->assignedCustomer->closeAssignment();
    }
    public function test_closeAssignment_setStatusClosed()
    {
        $this->closeAssignment();
        $this->assertEquals(CustomerAssignmentStatus::GOOD_FUND, $this->assignedCustomer->status);
    }
    public function test_closeAssignment_alreadyConcluded_forbidden()
    {
        $this->assignedCustomer->status = CustomerAssignmentStatus::RECYCLED;
        $this->assertRegularExceptionThrowed(fn() => $this->closeAssignment(), 'Forbidden', 'assignment already concluded');
    }
    
    //
    protected function recycleAssignment()
    {
        $this->assignedCustomer->recycleAssignment();
    }
    public function test_recycleAssignment_setStatusRecycled()
    {
        $this->recycleAssignment();
        $this->assertEquals(CustomerAssignmentStatus::RECYCLED, $this->assignedCustomer->status);
    }
    public function test_recycleAssignment_alreadyConcluded_forbidden()
    {
        $this->assignedCustomer->status = CustomerAssignmentStatus::RECYCLED;
        $this->assertRegularExceptionThrowed(fn() => $this->recycleAssignment(), 'Forbidden', 'assignment already concluded');
    }
    
    //
    protected function isManageableByManager()
    {
        return $this->assignedCustomer->isManageableByManager($this->manager);
    }
    public function test_isManageableByManager_returnSalesAssociationResult()
    {
        $this->sales->expects($this->once())
                ->method('isManageableByManager')
                ->with($this->manager);
        $this->isManageableByManager();
    }
}

class TestableAssignedCustomer extends AssignedCustomer
{
    public Sales $sales;
    public string $id;
    public CustomerAssignmentStatus $status;
    
    function __construct()
    {
        parent::__construct();
        $this->status = CustomerAssignmentStatus::ACTIVE;
    }
}
