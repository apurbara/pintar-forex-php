<?php

namespace Sales\Domain\Task\GreetingAssignment;

use Sales\Domain\DependencyModel\CustomerData;
use Tests\Sales\Domain\Task\SalesTaskTestBase;

class UpdateCustomerTest extends SalesTaskTestBase
{
    protected $task;
    protected $payload, $customerData;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareGreetingAssignmentDependency();
        $this->prepareCityDependency();
        //
        $this->task = new UpdateCustomer($this->greetingAssignmentRepository, $this->cityRepository);
        
        $this->customerData = (new CustomerData('name', 'address@email.org', '082132123123'))
                ->setCityId($this->cityId);
        $this->payload = (new UpdateCustomerPayload())
                ->setId($this->greetingAssignmentId)
                ->setCustomerData($this->customerData);
    }
    
    //
    protected function buildPayload()
    {
        
    }
    protected function execute()
    {
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_updateCustomerOfGreetingAssignment()
    {
        $this->greetingAssignment->expects($this->once())
                ->method('updateCustomer')
                ->with($this->customerData, $this->city);
        $this->execute();
    }
    public function test_execute_assertAssignmentBelongsToSales()
    {
        $this->greetingAssignment->expects($this->once())
                ->method('assertBelongsToSales')
                ->with($this->sales);
        $this->execute();
    }
    public function test_execute_nullCityId()
    {
        $this->customerData = (new CustomerData('name', 'address@email.org', '082132123123'));
        $this->payload = (new UpdateCustomerPayload())
                ->setId($this->greetingAssignmentId)
                ->setCustomerData($this->customerData);
        $this->greetingAssignment->expects($this->once())
                ->method('updateCustomer')
                ->with($this->customerData, null);
        $this->task->executeBySales($this->sales, $this->payload);
    }
}
