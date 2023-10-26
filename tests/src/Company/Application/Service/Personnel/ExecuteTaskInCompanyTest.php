<?php

namespace Company\Application\Service\Personnel;

use Company\Domain\Model\Personnel;
use Company\Domain\Model\PersonnelTaskInCompany;
use Tests\TestBase;

class ExecuteTaskInCompanyTest extends TestBase
{
    protected $personnelRepository;
    protected $personnel;
    protected $personnelId = 'personnelId';
    protected $service;
    //
    protected $task;
    protected $payload = 'string respesent task payload';

    protected function setUp(): void
    {
        parent::setUp();
        $this->personnelRepository = $this->buildMockOfInterface(PersonnelRepository::class);
        $this->service = new ExecuteTaskInCompany($this->personnelRepository);
        
        $this->personnel = $this->buildMockOfClass(Personnel::class);
        
        $this->task = $this->buildMockOfInterface(PersonnelTaskInCompany::class);
    }
    
    //
    protected function execute()
    {
        $this->personnelRepository->expects($this->any())
                ->method('ofId')
                ->with($this->personnelId)
                ->willReturn($this->personnel);
        
        $this->service->execute($this->personnelId, $this->task, $this->payload);
    }
    public function test_execute_personnelExecuteTaskInCompany()
    {
        $this->personnel->expects($this->once())
                ->method('executeTaskInCompany')
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
