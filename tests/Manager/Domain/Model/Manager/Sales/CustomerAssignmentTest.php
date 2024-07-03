<?php

namespace Manager\Domain\Model\Manager\Sales;

use DateTimeImmutable;
use Manager\Domain\DependencyModel\Customer;
use Manager\Domain\DependencyModel\CustomerJourney;
use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use SharedContext\Domain\Event\CustomerAssignedEvent;
use SharedContext\Domain\Event\InHouseSalesCustomerAssignmentRecycledEvent;
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
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = $this->buildMockOfClass(Sales::class);
        $this->customer = $this->buildMockOfClass(Customer::class);
        $this->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
        //
        $this->customerAssignment = new TestableCustomerAssignment($this->sales, $this->customer, $this->customerJourney, 'id');
        $this->customerAssignment->recordedEvents = [];
        //
        $this->manager = $this->buildMockOfClass(Manager::class);
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
    public function test_construct_assertCustomerActive()
    {
        $this->customer->expects($this->once())
                ->method('assertActive');
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
    
    //
    protected function closeAssignment()
    {
        $this->customerAssignment->closeAssignment();
    }
    public function test_closeAssignment_setStatusClosed()
    {
        $this->closeAssignment();
        $this->assertEquals(CustomerAssignmentStatus::GOOD_FUND, $this->customerAssignment->status);
    }
    public function test_closeAssignment_alreadyConcluded_forbidden()
    {
        $this->customerAssignment->status = CustomerAssignmentStatus::RECYCLED;
        $this->assertRegularExceptionThrowed(fn() => $this->closeAssignment(), 'Forbidden', 'assignment already concluded');
    }
    
    //
    protected function recycle()
    {
        $this->sales->expects($this->any())->method('isInHouseSales')->willReturn(true);
        $this->customer->expects($this->any())->method('getId')->willReturn($this->customerId);
        $this->customerAssignment->recycle();
    }
    public function test_recycle_setStatusRecycled()
    {
        $this->recycle();
        $this->assertEquals(CustomerAssignmentStatus::RECYCLED, $this->customerAssignment->status);
    }
    public function test_recycle_alreadyConcluded_forbidden()
    {
        $this->customerAssignment->status = CustomerAssignmentStatus::RECYCLED;
        $this->assertRegularExceptionThrowed(fn() => $this->recycle(), 'Forbidden', 'assignment already concluded');
    }
    public function test_recycle_storeAssignmentRecycledevent()
    {
        $this->recycle();
        
        $event = new InHouseSalesCustomerAssignmentRecycledEvent($this->customerId);
        $this->assertEquals($event, $this->customerAssignment->pullRecordedEvents()[0]);
    }
    public function test_recycle_notInHouseSales_dontStoreEvent()
    {
        $this->sales->expects($this->once())->method('isInHouseSales')->willReturn(false);
        $this->recycle();
        $this->assertEmpty($this->customerAssignment->pullRecordedEvents());
    }
    
    //
    protected function cancel()
    {
        $this->customerAssignment->cancel();
    }
    public function test_cancel_setStatusCancelled()
    {
        $this->cancel();
        $this->assertEquals(CustomerAssignmentStatus::CANCELLED, $this->customerAssignment->status);
    }
    
    //
    protected function belongsToManager()
    {
        return $this->customerAssignment->belongsToManager($this->manager);
    }
    public function test_belongsToManager_returnSalesManagerOwnershipComparisonResult()
    {
        $this->sales->expects($this->once())
                ->method('belongsToManager')
                ->with($this->manager)
                ->willReturn(true);
        $this->assertTrue($this->belongsToManager());
    }
    
    //
    protected function assertBelongsToManager()
    {
        $this->customerAssignment->assertBelongsToManager($this->manager);
    }
    public function test_assertBelongsToManager_salesDoesNotBelongsToManager_forbidden()
    {
        $this->assertRegularExceptionThrowed(fn() => $this->assertBelongsToManager(), 'Forbidden', 'customer assignment does not belongs to manager');
    }
    public function test_assertBelongsToManager_salesBelongsToManager_void()
    {
        $this->sales->expects($this->once())
                ->method('belongsToManager')
                ->with($this->manager)
                ->willReturn(true);
        $this->assertBelongsToManager();
        $this->markAsSuccess();
    }
}

class TestableCustomerAssignment extends CustomerAssignment
{
    public Sales $sales;
    public Customer $customer;
    public ?CustomerJourney $customerJourney;
    public string $id;
    public DateTimeImmutable $createdTime;
    public CustomerAssignmentStatus $status;
    public $recordedEvents;
}
