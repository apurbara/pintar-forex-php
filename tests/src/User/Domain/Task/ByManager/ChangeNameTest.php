<?php

namespace User\Domain\Task\ByManager;

use Tests\src\User\Domain\Task\ByManager\ManagerTaskTestBase;

class ChangeNameTest extends ManagerTaskTestBase
{
    protected $task;
    protected $name = 'new name';
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->task = new ChangeName();
    }
    
    //
    protected function execute()
    {
        $this->task->executeByManager($this->manager, $this->name);
    }
    public function test_execute_changeManagerName()
    {
        $this->manager->expects($this->once())
                ->method('changeName')
                ->with($this->name);
        $this->execute();
    }
}
