<?php

namespace Sales\Domain\Task\AssignedCustomer;

use Sales\Domain\Model\AreaStructure\Area\CustomerData;
use Tests\src\Sales\Domain\Task\SalesTaskTestBase;

class UpdateCustomerTest extends SalesTaskTestBase
{
    protected $task;
    protected $payload, $customerData;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareAssignedCustomerDependency();
        $this->prepareAreaDependency();
        //
        $this->task = new UpdateCustomer($this->assignedCustomerRepository, $this->areaRepository);
        
        $this->customerData = (new CustomerData('name', 'address@email.org', '082132123123'))
                ->setAreaId($this->areaId);
        $this->payload = (new UpdateCustomerPayload())
                ->setId($this->assignedCustomerId)
                ->setCustomerData($this->customerData);
    }
    
    //
    protected function execute()
    {
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_updateCustomerOfAssignedCustomer()
    {
        $this->assignedCustomer->expects($this->once())
                ->method('updateCustomer')
                ->with($this->area, $this->customerData);
        $this->execute();
    }
    public function test_execute_assertAssignmentBelongsToSales()
    {
        $this->assignedCustomer->expects($this->once())
                ->method('assertBelongsToSales')
                ->with($this->sales);
        $this->execute();
    }
}
