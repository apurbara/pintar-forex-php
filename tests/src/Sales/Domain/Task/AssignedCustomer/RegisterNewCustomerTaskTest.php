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
        $this->prepareCustomerJourneyDependency();

        $this->task = new RegisterNewCustomerTask($this->assignedCustomerRepository, $this->areaRepository,
                $this->customerRepository, $this->customerJourneyRepository, $this->dispatcher);
        //
        $this->payload = new RegisterNewCustomerPayload($this->areaId, new CustomerData('name', 'address@email.org', '0823123123123'));
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
                ->method('isPhoneAvailable')
                ->willReturn(true);
        $this->customerJourneyRepository->expects($this->any())
                ->method('anInitialCustomerJourney')
                ->willReturn($this->customerJourney);
        $this->sales->expects($this->any())
                ->method('registerNewCustomer')
                ->willReturn($this->assignedCustomer);

        $this->task->executeBySales($this->sales, $this->payload);
    }

    public function test_execute_addAssignedCustomerCreatedBySalesToRepository()
    {
        $this->sales->expects($this->once())
                ->method('registerNewCustomer')
                ->with($this->area, $this->customerJourney, $this->assignedCustomerId, $this->payload->customerData)
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

    public function test_execute_customerPhoneUnavailable_conflict()
    {
        $this->customerRepository->expects($this->once())
                ->method('isPhoneAvailable')
                ->with($this->payload->customerData->phone)
                ->willReturn(false);
        $this->assertRegularExceptionThrowed(fn() => $this->execute(), 'Conflict', 'phone already registered');
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
