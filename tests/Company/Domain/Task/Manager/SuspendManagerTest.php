<?php

namespace Company\Domain\Task\Manager;

use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class SuspendManagerTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareManagerDependency();
        //
        $this->task = new SuspendManager($this->managerRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->managerId);
    }
    public function test_execute_suspendManager()
    {
        $this->manager->expects($this->once())
                ->method('suspend');
        $this->execute();
    }
}
