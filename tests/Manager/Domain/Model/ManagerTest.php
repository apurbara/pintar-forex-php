<?php

namespace Manager\Domain\Model;

use DateTimeImmutable;
use Manager\Domain\Task\ManagerTask;
use SharedContext\Domain\ValueObject\AccountInfo;
use Tests\TestBase;

class ManagerTest extends TestBase
{
    protected $manager;
    protected $payload = 'task payload', $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new TestableManager();
        //
        $this->task = $this->buildMockOfInterface(ManagerTask::class);
    }
    
    //
    protected function executeTask()
    {
        $this->manager->executeTask($this->task, $this->payload);
    }
    public function test_executeTask_executeTask()
    {
        $this->task->expects($this->once())
                ->method('executeByManager')
                ->with($this->manager, $this->payload);
        $this->executeTask();
    }
    public function test_executeTask_suspendedManager()
    {
        $this->manager->suspended = true;
        $this->assertRegularExceptionThrowed(fn() => $this->executeTask(), 'Forbidden', 'only active manager can make this request');
    }
}

class TestableManager extends Manager
{
    public string $id = 'id';
    public bool $suspended = false;
    public DateTimeImmutable $createdTime;
    public AccountInfo $accountInfo;
    
    function __construct()
    {
        parent::__construct();
    }
}
