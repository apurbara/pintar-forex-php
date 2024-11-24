<?php

namespace Manager\Domain\Model\Manager\Sales;

use Manager\Domain\DependencyModel\Customer;
use Manager\Domain\Model\Manager\Sales;
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
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = $this->buildMockOfClass(Sales::class);
        $this->customer = $this->buildMockOfClass(Customer::class);
        //
        $this->greetingAssignment = new TestableGreetingAssignment($this->sales, $this->customer, 'id');
        $this->assignment = $this->buildMockOfClass(CustomerAssignment::class);
        $this->greetingAssignment->customerAssignment = $this->assignment;
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
    public function test_construct_assertCustomerStatusEqualsNew()
    {
        $this->customer->expects($this->once())
                ->method('assertStatusEquals')
                ->with(CustomerStatus::NEW);
        $this->construct();
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
