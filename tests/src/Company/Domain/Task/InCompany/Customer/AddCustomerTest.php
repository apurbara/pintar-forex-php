<?php

namespace Company\Domain\Task\InCompany\Customer;

use Company\Domain\Model\AreaStructure\Area\CustomerData;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class AddCustomerTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCustomerDependency();
        $this->prepareAreaDependency();
        //
        $this->task = new AddCustomer($this->customerRepository, $this->areaRepository);
        $this->payload = (new CustomerData())
                ->setAreaId($this->areaId)
                ->setPhone('082131231231')
                ->setName('name');
    }
    
    //
    protected function execute()
    {
        $this->customerRepository->expects($this->any())
                ->method('isPhoneAvailable')
                ->willReturn(true);
        $this->customerRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->customerId);
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_addCustomerToRepository()
    {
        $this->customerRepository->expects($this->once())
                ->method('add');
        $this->execute();
    }
    public function test_execute_setPayloadId()
    {
        $this->execute();
        $this->assertSame($this->customerId, $this->payload->id);
    }
    public function test_execute_noAreaIdSet_200()
    {
        $this->payload = (new CustomerData())
                ->setName('name')
                ->setPhone('08213123123');
        $this->execute();
        $this->markAsSuccess();
    }
    public function test_execute_phoneUnavailable_forbidden()
    {
        $this->customerRepository->expects($this->once())
                ->method('isPhoneAvailable')
                ->with($this->payload->phone)
                ->willReturn(false);
        $this->assertRegularExceptionThrowed(fn() => $this->execute(), "Conflict", 'customer phone already registered');
    }
}
