<?php

namespace Company\Application\Service\Manager;

use Company\Domain\Model\Manager;
use Company\Domain\Model\ManagerTaskInCompany;
use Tests\TestBase;

class ExecuteTaskInCompanyTest extends TestBase
{
    protected $managerRepository, $manager, $managerId = 'managerId';
    protected $service;
    protected $payload = 'task payload', $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->managerRepository = $this->buildMockOfInterface(ManagerRepository::class);
        $this->manager = $this->buildMockOfClass(Manager::class);
        $this->service = new ExecuteTaskInCompany($this->managerRepository);
        $this->task = $this->buildMockOfInterface(ManagerTaskInCompany::class);
    }
    
    //
    protected function execute()
    {
        $this->managerRepository->expects($this->any())
                ->method('ofId')
                ->with($this->managerId)
                ->willReturn($this->manager);
        $this->service->execute($this->managerId, $this->task, $this->payload);
    }
    public function test_execute_managerExecuteTask()
    {
        $this->manager->expects($this->once())
                ->method('executeTaskInCompany')
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
