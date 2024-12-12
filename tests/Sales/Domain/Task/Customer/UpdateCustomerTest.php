<?php

namespace Sales\Domain\Task\Customer;

use Sales\Domain\DependencyModel\CustomerData;
use Sales\Domain\Model\Sales\ContainCustomerAssignmentInterface;
use Sales\Domain\Task\Dependency\ContainCustomerAssignmentRepository;
use Tests\Sales\Domain\Task\SalesTaskTestBase;

class UpdateCustomerTest extends SalesTaskTestBase
{
    protected $customerAssignmentRepository, $customerAssignment, $customerAssignmentId = 'customerAssignmentId';
    protected $task;
    protected $payload, $customerData;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCityDependency();
        //
        $this->customerAssignmentRepository = $this->buildMockOfInterface(ContainCustomerAssignmentRepository::class);
        $this->customerAssignment = $this->buildMockOfInterface(ContainCustomerAssignmentInterface::class);
        $this->customerAssignmentRepository->expects($this->any())
                ->method('ofId')
                ->with($this->customerAssignmentId)
                ->willReturn($this->customerAssignment);
        
        $this->task = new UpdateCustomer($this->customerAssignmentRepository, $this->cityRepository);
        
        $this->customerData = (new CustomerData())
                ->setCityId($this->cityId);
        $this->payload = (new UpdateCustomerPayload())
                ->setCustomerAssignmentId($this->customerAssignmentId)
                ->setCustomerData($this->customerData);
    }
    
    //
    protected function execute()
    {
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_updateCustomerOfGreetingAssignment()
    {
        $this->customerAssignment->expects($this->once())
                ->method('updateCustomer')
                ->with($this->customerData, $this->city);
        $this->execute();
    }
    public function test_execute_assertAssignmentBelongsToSales()
    {
        $this->customerAssignment->expects($this->once())
                ->method('assertBelongsToSales')
                ->with($this->sales);
        $this->execute();
    }
    public function test_execute_nullCityId()
    {
        $this->customerData = (new CustomerData);
        $this->payload = (new UpdateCustomerPayload())
                ->setCustomerAssignmentId($this->customerAssignmentId)
                ->setCustomerData($this->customerData);
        $this->customerAssignment->expects($this->once())
                ->method('updateCustomer')
                ->with($this->customerData, null);
        $this->task->executeBySales($this->sales, $this->payload);
    }
}
