<?php

namespace User\Domain\Task\ByPersonnel;

use SharedContext\Domain\ValueObject\ChangeUserPasswordData;
use Tests\src\User\Domain\Task\ByPersonnel\PersonnelTaskTestBase;

class ChangePasswordTest extends PersonnelTaskTestBase
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
        $this->task->executeByPersonnel($this->personnel, $this->payload);
    }
    public function test_execute_changePersonnelPassword()
    {
        $this->personnel->expects($this->once())
                ->method('changePassword')
                ->with($this->payload);
        $this->execute();
    }
}
