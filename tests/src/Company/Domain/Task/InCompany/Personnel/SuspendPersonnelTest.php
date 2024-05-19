<?php

namespace Company\Domain\Task\InCompany\Personnel;

use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class SuspendPersonnelTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->preparePersonnelDependency();
        //
        $this->task = new SuspendPersonnel($this->personnelRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->personnelId);
    }
    public function test_execute_suspendPersonnel()
    {
        $this->personnel->expects($this->once())
                ->method('suspend');
        $this->execute();
    }
}
