<?php

namespace User\Domain\Task\ByManager;

use SharedContext\Domain\ValueObject\ChangeUserPasswordData;
use Tests\src\User\Domain\Task\ByManager\ManagerTaskTestBase;

class ChangePasswordTest extends ManagerTaskTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->task = new ChangePassword();
        $this->payload = new ChangeUserPasswordData('password123', 'newPassword123');
    }
    
    //
    protected function execute()
    {
        $this->task->executeByManager($this->manager, $this->payload);
    }
    public function test_execute_changeManagerPassword()
    {
        $this->manager->expects($this->once())
                ->method('changePassword')
                ->with($this->payload);
        $this->execute();
    }
}
