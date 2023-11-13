<?php

namespace Manager\Domain\Model\Personnel;

use Manager\Domain\Model\Personnel;
use Manager\Domain\Task\ManagerTask;
use Tests\TestBase;

class ManagerTest extends TestBase
{
    protected $manager;
    //
    protected $task, $payload = 'task payload';

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
    public function test_executeTask_inactiveManager_forbidden()
    {
        $this->manager->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->executeTask(), 'Forbidden', 'only active manager can make this request');
    }
}

class TestableManager extends Manager
{
    public Personnel $personnel;
    public string $id = 'id';
    public bool $disabled = false;
    
    public function __construct()
    {
        parent::__construct();
    }
}
