<?php

namespace User\Application\Service\Personnel;

use Tests\TestBase;
use User\Domain\Model\Personnel;
use User\Domain\Task\ByPersonnel\PersonnelTask;

class ExecutePersonnelTaskTest extends TestBase
{
    protected $personnelRepository;
    protected $personnel;
    protected $personnelId = 'personnelId';
    //
    protected $service;
    protected $task, $payload = 'string represent task payload';

    protected function setUp(): void
    {
        parent::setUp();
        $this->personnelRepository = $this->buildMockOfInterface(PersonnelRepository::class);
        $this->personnel = $this->buildMockOfClass(Personnel::class);
        
        $this->service = new ExecutePersonnelTask($this->personnelRepository);
        //
        $this->task = $this->buildMockOfInterface(PersonnelTask::class);
    }
    
    //
    protected function execute()
    {
        $this->personnelRepository->expects($this->once())
                ->method('ofId')
                ->with($this->personnelId)
                ->willReturn($this->personnel);
        $this->service->execute($this->personnelId, $this->task, $this->payload);
    }
    public function test_execute_personnelExecuteTask()
    {
        $this->personnel->expects($this->once())
                ->method('executeTask')
                ->with($this->task, $this->payload);
        $this->execute();
    }
    public function test_execute_updateRepository()
    {
        $this->personnelRepository->expects($this->once())
                ->method('update');
        $this->execute();
    }
}
