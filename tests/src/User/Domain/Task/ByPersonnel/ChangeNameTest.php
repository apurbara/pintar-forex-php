<?php

namespace User\Domain\Task\ByPersonnel;

use Tests\src\User\Domain\Task\ByPersonnel\PersonnelTaskTestBase;

class ChangeNameTest extends PersonnelTaskTestBase
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
        $this->task->executeByPersonnel($this->personnel, $this->name);
    }
    public function test_execute_changePersonnelName()
    {
        $this->personnel->expects($this->once())
                ->method('changeName')
                ->with($this->name);
        $this->execute();
    }
}
