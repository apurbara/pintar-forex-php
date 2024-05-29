<?php

namespace Sales\Domain\Task\BySales\CustomerAssignment;

use Sales\Domain\DependencyModel\AreaStructure\Area\CustomerData;
use Tests\src\Sales\Domain\Task\BySales\SalesTaskTestBase;

class RegisterNewCustomerTaskTest extends SalesTaskTestBase
{

    protected $task;
    protected $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCustomerAssignmentDependency();
        $this->prepareAreaDependency();
        $this->prepareCustomerDependency();
        $this->prepareCustomerJourneyDependency();

        $this->task = new RegisterNewCustomerTask($this->customerAssignmentRepository, $this->areaRepository,
                $this->customerRepository, $this->customerJourneyRepository, $this->dispatcher);
        //
        $this->payload = new RegisterNewCustomerPayload($this->areaId,
                new CustomerData('name', 'address@email.org', '0823123123123'));
    }

    //
    protected function execute()
    {
        $this->customerAssignmentRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->customerAssignmentId);
        $this->customerRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->customerId);
        $this->customerRepository->expects($this->any())
                ->method('isPhoneAvailable')
                ->with($this->payload->customerData->phone)
                ->willReturn(true);
        $this->customerJourneyRepository->expects($this->any())
                ->method('anInitialCustomerJourney')
                ->willReturn($this->customerJourney);

        $this->task->executeBySales($this->sales, $this->payload);
    }

    public function test_execute_setPayloadId()
    {
        $this->execute();
        $this->assertSame($this->customerAssignmentId, $this->payload->id);
    }

    public function test_execute_setCustomerDataId()
    {
        $this->execute();
        $this->assertSame($this->customerId, $this->payload->customerData->id);
    }

    public function test_execute_addCustomerAssignmentCreatedBySalesToRepository()
    {
        $this->customerAssignmentRepository->expects($this->once())
                ->method('add');
        $this->execute();
    }

    public function test_execute_customerPhoneUnavailable_conflict()
    {
        $this->customerRepository->expects($this->once())
                ->method('isPhoneAvailable')
                ->with($this->payload->customerData->phone)
                ->willReturn(false);
        $this->assertRegularExceptionThrowed(fn() => $this->execute(), 'Conflict', 'phone already registered');
    }

    public function test_execute_dispatchCustomerAssignment()
    {
        $this->dispatcher->expects($this->once())
                ->method('dispatchEventContainer');
        $this->execute();
    }

}
