<?php

namespace Sales\Domain\Model\Personnel\Sales;

use DateTimeImmutable;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\Personnel\Sales;
use SharedContext\Domain\Event\CustomerAssignedEvent;
use Tests\TestBase;

class AssignedCustomerTest extends TestBase
{
    protected $sales;
    protected $customer;
    protected $assignedCustomer;
    //
    protected $id = 'newId', $customerData;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = $this->buildMockOfClass(Sales::class);
        $this->customer = $this->buildMockOfClass(Customer::class);
        
        $this->assignedCustomer = new TestableAssignedCustomer($this->sales, $this->customer, 'id');
        
    }
    
    //
    protected function construct()
    {
        return new TestableAssignedCustomer($this->sales, $this->customer, $this->id);
    }
    public function test_construct_setProperties()
    {
        $assignedCustomer = $this->construct();
        $this->assertSame($this->sales, $assignedCustomer->sales);
        $this->assertSame($this->customer, $assignedCustomer->customer);
        $this->assertSame($this->id, $assignedCustomer->id);
        $this->assertFalse($assignedCustomer->disabled);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($assignedCustomer->createdTime);
    }
    public function test_construct_storeCustomerAssignedEvent()
    {
        $assignedCustomer = $this->construct();
        $event = new CustomerAssignedEvent($this->id);
        $this->assertEquals($event, $assignedCustomer->recordedEvents[0]);
    }
}

class TestableAssignedCustomer extends AssignedCustomer
{
    public Sales $sales;
    public Customer $customer;
    public string $id;
    public bool $disabled;
    public DateTimeImmutable $createdTime;
    public $recordedEvents = [];
}
