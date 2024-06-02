<?php

namespace User\Application\Service\Sales;

use Tests\TestBase;
use User\Domain\Model\Sales;
use User\Domain\Task\BySales\SalesTask;

class ExecuteSalesTaskTest extends TestBase
{
    protected $salesRepository;
    protected $sales;
    protected $salesId = 'salesId';
    //
    protected $service;
    protected $task, $payload = 'string represent task payload';

    protected function setUp(): void
    {
        parent::setUp();
        $this->salesRepository = $this->buildMockOfInterface(SalesRepository::class);
        $this->sales = $this->buildMockOfClass(Sales::class);
        
        $this->service = new ExecuteSalesTask($this->salesRepository);
        //
        $this->task = $this->buildMockOfInterface(SalesTask::class);
    }
    
    //
    protected function execute()
    {
        $this->salesRepository->expects($this->once())
                ->method('ofId')
                ->with($this->salesId)
                ->willReturn($this->sales);
        $this->service->execute($this->salesId, $this->task, $this->payload);
    }
    public function test_execute_salesExecuteTask()
    {
        $this->sales->expects($this->once())
                ->method('executeTask')
                ->with($this->task, $this->payload);
        $this->execute();
    }
    public function test_execute_updateRepository()
    {
        $this->salesRepository->expects($this->once())
                ->method('update');
        $this->execute();
    }
}
