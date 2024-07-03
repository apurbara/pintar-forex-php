<?php

namespace Company\Domain\Task\Manager;

use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class UnsuspendManagerTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareManagerDependency();
        //
        $this->task = new UnsuspendManager($this->managerRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->managerId);
    }
    public function test_execute_suspendManager()
    {
        $this->manager->expects($this->once())
                ->method('unsuspend');
        $this->execute();
    }
}
