<?php

namespace Company\Domain\Task\CustomerVerification;

use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class DisableCustomerVerificationTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCustomerVerificationDependency();
        //
        $this->task = new DisableCustomerVerification($this->customerVerificationRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->customerVerificationId);
    }
    public function test_execute_updateCustomerVerification()
    {
        $this->customerVerification->expects($this->once())
                ->method('disable');
        $this->execute();
    }
}
