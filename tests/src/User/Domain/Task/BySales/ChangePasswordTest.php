<?php

namespace User\Domain\Task\BySales;

use SharedContext\Domain\ValueObject\ChangeUserPasswordData;
use Tests\src\User\Domain\Task\BySales\SalesTaskTestBase;

class ChangePasswordTest extends SalesTaskTestBase
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
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_changeSalesPassword()
    {
        $this->sales->expects($this->once())
                ->method('changePassword')
                ->with($this->payload);
        $this->execute();
    }
}
