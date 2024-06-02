<?php

namespace User\Domain\Task\BySales;

use Tests\src\User\Domain\Task\BySales\SalesTaskTestBase;

class ChangeNameTest extends SalesTaskTestBase
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
        $this->task->executeBySales($this->sales, $this->name);
    }
    public function test_execute_changeSalesName()
    {
        $this->sales->expects($this->once())
                ->method('changeName')
                ->with($this->name);
        $this->execute();
    }
}
