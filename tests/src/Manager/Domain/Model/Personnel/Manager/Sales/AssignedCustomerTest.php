<?php

namespace Manager\Domain\Model\Personnel\Manager\Sales;

use Manager\Domain\Model\AreaStructure\Area\Customer;
use Manager\Domain\Model\CustomerJourney;
use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Model\Personnel\Manager\Sales;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use SharedContext\Domain\Enum\SalesType;
use SharedContext\Domain\Event\CustomerAssignedEvent;
use SharedContext\Domain\Event\InHouseSalesCustomerAssignmentRecycledEvent;
use Tests\TestBase;

class AssignedCustomerTest extends TestBase
{
    protected $sales;
    protected $customer, $customerId = 'customerId';
    protected $customerJourney;
    protected $assignedCustomer;
    //
    protected $id = 'newId';
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = $this->buildMockOfClass(Sales::class);
        $this->customer = $this->buildMockOfClass(Customer::class);
        $this->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
        //
        $this->assignedCustomer = new TestableAssignedCustomer($this->sales, $this->customer, $this->customerJourney, 'id');
        $this->assignedCustomer->recordedEvents = [];
        //
        $this->manager = $this->buildMockOfClass(Manager::class);
    }
    
    //
    protected function construct()
    {
        return new TestableAssignedCustomer($this->sales, $this->customer, $this->customerJourney, $this->id);
    }
    public function test_construct_setProperties()
    {
        $assignment = $this->construct();
        $this->assertSame($this->sales, $assignment->sales);
        $this->assertSame($this->customer, $assignment->customer);
        $this->assertSame($this->customerJourney, $assignment->customerJourney);
        $this->assertSame($this->id, $assignment->id);
        $this->assertSame(CustomerAssignmentStatus::ACTIVE, $assignment->status);
    }
    public function test_construct_assertCustomerJourneyActive()
    {
        $this->customerJourney->expects($this->once())
                ->method('assertActive');
        $this->construct();
    }
    public function test_construct_storeCustomerAssignedEvent()
    {
        $assignment = $this->construct();
        $this->assertEquals(new CustomerAssignedEvent($this->id), $assignment->pullRecordedEvents()[0]);
    }
    public function test_construct_assertCustomerHasNoActiveAssignment()
    {
        $this->customer->expects($this->once())
                ->method('assertHasNoActiveAssignment');
        $this->construct();
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
        $this->sales->expects($this->any())->method('getType')->willReturn(SalesType::IN_HOUSE);
        $this->customer->expects($this->any())->method('getId')->willReturn($this->customerId);
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
    public function test_recycleAssignment_storeAssignmentRecycledevent()
    {
        $this->recycleAssignment();
        
        $event = new InHouseSalesCustomerAssignmentRecycledEvent($this->customerId);
        $this->assertEquals($event, $this->assignedCustomer->pullRecordedEvents()[0]);
    }
    public function test_recycleAssignment_notInHouseSales_dontStoreEvent()
    {
        $this->sales->expects($this->once())->method('getType')->willReturn(SalesType::FREELANCE);
        $this->recycleAssignment();
        $this->assertEmpty($this->assignedCustomer->pullRecordedEvents());
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
    public Customer $customer;
    public CustomerJourney $customerJourney;
    public string $id;
    public CustomerAssignmentStatus $status;
    public $recordedEvents;
}
