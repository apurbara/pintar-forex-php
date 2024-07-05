<?php

namespace Sales\Domain\Task\CustomerAssignment;

use Sales\Domain\DependencyModel\CustomerData;
use Tests\Sales\Domain\Task\SalesTaskTestBase;

class RegisterNewCustomerTaskTest extends SalesTaskTestBase
{

    protected $task;
    protected $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCustomerAssignmentDependency();
        $this->prepareCityDependency();
        $this->prepareCustomerDependency();
        $this->prepareCustomerJourneyDependency();

        $this->task = new RegisterNewCustomerTask($this->customerAssignmentRepository, $this->cityRepository,
                $this->customerRepository, $this->customerJourneyRepository, $this->dispatcher);
        //
        $customerData = (new CustomerData())
                ->setCityId($this->cityId)
                ->setName('name')
                ->setEmail('address@email.org')
                ->setPhone('08213123123');
        $this->payload = (new RegisterNewCustomerPayload())
                ->setCustomerData($customerData);
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
