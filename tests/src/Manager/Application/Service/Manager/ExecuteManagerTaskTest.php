<?php

namespace Manager\Application\Service\Manager;

use Manager\Domain\Model\Personnel\Manager;
use Tests\TestBase;

class ExecuteManagerTaskTest extends TestBase
{
    protected $managerRepository;
    protected $manager;
    protected $managerId = 'managerId', $personnelId = 'personnelId';
    //
    protected $service;
    protected $task, $payload = 'task payload';

    protected function setUp(): void
    {
        parent::setUp();
        $this->managerRepository = $this->buildMockOfInterface(ManagerRepository::class);
        $this->manager = $this->buildMockOfClass(Manager::class);
        $this->managerRepository->expects($this->any())
                ->method('aManagerBelongsToPersonnel')
                ->with($this->personnelId, $this->managerId)
                ->willReturn($this->manager);
        //
        $this->service = new ExecuteManagerTask($this->managerRepository);
        $this->task = $this->buildMockOfInterface(\Manager\Domain\Task\ManagerTask::class);
    }
    
    //
    protected function execute()
    {
        $this->service->excute($this->personnelId, $this->managerId, $this->task, $this->payload);
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
