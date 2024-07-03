<?php

namespace Company\Domain\Task\CustomerVerification;

use Company\Domain\Model\CustomerVerificationData;
use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class UpdateCustomerVerificationTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCustomerVerificationDependency();
        //
        $this->task = new UpdateCustomerVerification($this->customerVerificationRepository);
        $this->payload = (new CustomerVerificationData($this->createLabelData()))
                ->setId($this->customerVerificationId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_updateCustomerVerification()
    {
        $this->customerVerification->expects($this->once())
                ->method('update')
                ->with($this->payload);
        $this->execute();
    }
}
