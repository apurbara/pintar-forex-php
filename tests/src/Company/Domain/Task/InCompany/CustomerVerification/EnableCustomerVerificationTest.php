<?php

namespace Company\Domain\Task\InCompany\CustomerVerification;

use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class EnableCustomerVerificationTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCustomerVerificationDependency();
        //
        $this->task = new EnableCustomerVerification($this->customerVerificationRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->customerVerificationId);
    }
    public function test_execute_updateCustomerVerification()
    {
        $this->customerVerification->expects($this->once())
                ->method('enable');
        $this->execute();
    }
}
