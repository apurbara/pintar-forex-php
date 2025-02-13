<?php

namespace Sales\Domain\Task\CustomerAssignment;

use Sales\Domain\DependencyModel\CustomerData;
use Tests\Sales\Domain\Task\SalesTaskTestBase;

class UpdateCustomerTest extends SalesTaskTestBase
{
    protected $task;
    protected $payload, $customerData;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCustomerAssignmentDependency();
        $this->prepareCityDependency();
        //
        $this->task = new UpdateCustomer($this->customerAssignmentRepository, $this->cityRepository);
        
        $this->customerData = (new CustomerData('name', 'address@email.org', '082132123123'))
                ->setCityId($this->cityId);
        $this->payload = (new UpdateCustomerPayload())
                ->setId($this->customerAssignmentId)
                ->setCustomerData($this->customerData);
    }
    
    //
    protected function execute()
    {
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_updateCustomerOfCustomerAssignment()
    {
        $this->customerAssignment->expects($this->once())
                ->method('updateCustomer')
                ->with($this->city, $this->customerData);
        $this->execute();
    }
    public function test_execute_assertAssignmentBelongsToSales()
    {
        $this->customerAssignment->expects($this->once())
                ->method('assertBelongsToSales')
                ->with($this->sales);
        $this->execute();
    }
    public function test_execute_emptyCity()
    {
        $this->customerData = new CustomerData();
        $this->payload = (new UpdateCustomerPayload())
                ->setId($this->customerAssignmentId)
                ->setCustomerData($this->customerData);
        $this->execute();
        $this->markAsSuccess();
    }
}
