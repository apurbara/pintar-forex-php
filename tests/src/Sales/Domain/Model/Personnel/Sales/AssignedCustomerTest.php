<?php

namespace Sales\Domain\Model\Personnel\Sales;

use DateTimeImmutable;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\ScheduledSalesActivityData;
use Sales\Domain\Model\SalesActivity;
use SharedContext\Domain\Event\CustomerAssignedEvent;
use SharedContext\Domain\ValueObject\HourlyTimeIntervalData;
use Tests\TestBase;

class AssignedCustomerTest extends TestBase
{
    protected $sales;
    protected $customer;
    protected $assignedCustomer;
    //
    protected $id = 'newId', $customerData;
    //
    protected $salesActivity;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = $this->buildMockOfClass(Sales::class);
        $this->customer = $this->buildMockOfClass(Customer::class);
        
        $this->assignedCustomer = new TestableAssignedCustomer($this->sales, $this->customer, 'id');
        //
        $this->salesActivity = $this->buildMockOfClass(SalesActivity::class);
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
    
    //
    protected function assertBelongsToSales()
    {
        $this->assignedCustomer->assertBelongsToSales($this->sales);
    }
    public function test_assertBelongsToSales_differentSales_forbidden()
    {
        $this->assignedCustomer->sales = $this->buildMockOfClass(Sales::class);
        $this->assertRegularExceptionThrowed(fn() => $this->assertBelongsToSales(), 'Forbidden', 'unmanaged assigned customer');
    }
    public function test_assertBelongsToSales_sameSales_void()
    {
        $this->assertBelongsToSales();
        $this->markAsSuccess();
    }
    
    //
    protected function submitSalesActivitySchedule()
    {
        $hourlyTimeIntervalData = new HourlyTimeIntervalData('next week');
        $scheduledSalesActivityData = (new ScheduledSalesActivityData($hourlyTimeIntervalData))->setId('scheduleId');
        return $this->assignedCustomer->submitSalesActivitySchedule($this->salesActivity, $scheduledSalesActivityData);
    }
    public function test_submitSalesActivitySchedule_returnScheduledSalesActivity()
    {
        $this->assertInstanceOf(AssignedCustomer\ScheduledSalesActivity::class, $this->submitSalesActivitySchedule());
    }
    public function test_submitSalesActivitySchedule_inactiveAssignment_forbidden()
    {
        $this->assignedCustomer->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->submitSalesActivitySchedule(), 'Forbidden', 'inactive customer assignment');
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
