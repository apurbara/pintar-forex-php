<?php

namespace Sales\Domain\Task\BySales\CustomerAssignment;

use Sales\Domain\DependencyModel\AreaStructure\Area\CustomerData;
use Tests\src\Sales\Domain\Task\BySales\SalesTaskTestBase;

class UpdateCustomerTest extends SalesTaskTestBase
{
    protected $task;
    protected $payload, $customerData;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCustomerAssignmentDependency();
        $this->prepareAreaDependency();
        //
        $this->task = new UpdateCustomer($this->customerAssignmentRepository, $this->areaRepository);
        
        $this->customerData = (new CustomerData('name', 'address@email.org', '082132123123'))
                ->setAreaId($this->areaId);
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
                ->with($this->area, $this->customerData);
        $this->execute();
    }
    public function test_execute_assertAssignmentBelongsToSales()
    {
        $this->customerAssignment->expects($this->once())
                ->method('assertBelongsToSales')
                ->with($this->sales);
        $this->execute();
    }
}
