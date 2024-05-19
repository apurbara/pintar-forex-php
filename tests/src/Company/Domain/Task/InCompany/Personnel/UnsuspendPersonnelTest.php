<?php

namespace Company\Domain\Task\InCompany\Personnel;

use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class UnsuspendPersonnelTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->preparePersonnelDependency();
        //
        $this->task = new UnsuspendPersonnel($this->personnelRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->personnelId);
    }
    public function test_execute_suspendPersonnel()
    {
        $this->personnel->expects($this->once())
                ->method('unsuspend');
        $this->execute();
    }
}
