<?php

namespace User\Application\Service\Manager;

use Tests\TestBase;
use User\Domain\Model\Manager;
use User\Domain\Task\ByManager\ManagerTask;

class ExecuteManagerTaskTest extends TestBase
{
    protected $managerRepository;
    protected $manager;
    protected $managerId = 'managerId';
    //
    protected $service;
    protected $task, $payload = 'string represent task payload';

    protected function setUp(): void
    {
        parent::setUp();
        $this->managerRepository = $this->buildMockOfInterface(ManagerRepository::class);
        $this->manager = $this->buildMockOfClass(Manager::class);
        
        $this->service = new ExecuteManagerTask($this->managerRepository);
        //
        $this->task = $this->buildMockOfInterface(ManagerTask::class);
    }
    
    //
    protected function execute()
    {
        $this->managerRepository->expects($this->once())
                ->method('ofId')
                ->with($this->managerId)
                ->willReturn($this->manager);
        $this->service->execute($this->managerId, $this->task, $this->payload);
    }
    public function test_execute_managerExecuteTask()
    {
        $this->manager->expects($this->once())
                ->method('executeTask')
                ->with($this->task, $this->payload);
        $this->execute();
    }
    public function test_execute_updateRepository()
    {
        $this->managerRepository->expects($this->once())
                ->method('update');
        $this->execute();
    }
}
