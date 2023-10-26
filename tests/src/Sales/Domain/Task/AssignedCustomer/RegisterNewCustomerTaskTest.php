<?php

namespace Sales\Domain\Task\AssignedCustomer;

use Sales\Domain\Model\AreaStructure\Area\CustomerData;
use Tests\src\Sales\Domain\Task\SalesTaskTestBase;

class RegisterNewCustomerTaskTest extends SalesTaskTestBase
{

    protected $task;
    protected $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareAssignedCustomerDependency();
        $this->prepareAreaDependency();
        $this->prepareCustomerDependency();

        $this->task = new RegisterNewCustomerTask($this->assignedCustomerRepository, $this->areaRepository,
                $this->customerRepository, $this->dispatcher);
        //
        $this->payload = new RegisterNewCustomerPayload($this->areaId, new CustomerData('name', 'address@email.org'));
    }

    //
    protected function execute()
    {
        $this->assignedCustomerRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->assignedCustomerId);
        $this->customerRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->customerId);
        $this->customerRepository->expects($this->any())
                ->method('isEmailAvailable')
                ->willReturn(true);
        $this->sales->expects($this->any())
                ->method('registerNewCustomer')
                ->willReturn($this->assignedCustomer);

        $this->task->executeBySales($this->sales, $this->payload);
    }

    public function test_execute_addAssignedCustomerCreatedBySalesToRepository()
    {
        $this->sales->expects($this->once())
                ->method('registerNewCustomer')
                ->with($this->area, $this->assignedCustomerId, $this->payload->customerData)
                ->willReturn($this->assignedCustomer);
        $this->assignedCustomerRepository->expects($this->once())
                ->method('add')
                ->with($this->assignedCustomer);
        $this->execute();
    }

    public function test_execute_setPayloadId()
    {
        $this->execute();
        $this->assertSame($this->assignedCustomerId, $this->payload->id);
    }

    public function test_execute_setCustomerDataId()
    {
        $this->execute();
        $this->assertSame($this->customerId, $this->payload->customerData->id);
    }

    public function test_execute_customerEmailUnavailable_conflict()
    {
        $this->customerRepository->expects($this->once())
                ->method('isEmailAvailable')
                ->with($this->payload->customerData->email)
                ->willReturn(false);
        $this->assertRegularExceptionThrowed(fn() => $this->execute(), 'Conflict', 'email already registered');
    }

    public function test_execute_dispatchAssignedCustomer()
    {
        $this->dispatcher->expects($this->once())
                ->method('dispatchEventContainer')
                ->with($this->assignedCustomer);
        $this->execute();
    }
    public function test_execute_assertAreaAccessible()
    {
        $this->area->expects($this->once())
                ->method('assertAccessible');
        $this->execute();
    }
}
