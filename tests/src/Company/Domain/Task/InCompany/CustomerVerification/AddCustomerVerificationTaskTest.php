<?php

namespace Company\Domain\Task\InCompany\CustomerVerification;

use Company\Domain\Model\CustomerVerificationData;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class AddCustomerVerificationTaskTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCustomerVerificationDependency();
        $this->task = new AddCustomerVerificationTask($this->customerVerificationRepository);
        //
        $this->payload = new CustomerVerificationData($this->createLabelData());
    }
    
    //
    protected function execute()
    {
        $this->customerVerificationRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->customerVerificationId);
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_addCustomerVerificationToRepository()
    {
        $this->customerVerificationRepository->expects($this->once())
                ->method('add');
        $this->execute();
    }
    public function test_execute_setPayloadId()
    {
        $this->execute();
        $this->assertSame($this->customerVerificationId, $this->payload->id);
    }
}
