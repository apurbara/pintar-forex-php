<?php

namespace Sales\Application\Service\Sales;

use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Task\SalesTask;
use Tests\TestBase;

class ExecuteSalesTaskTest extends TestBase
{

    protected $salesRepository;
    protected $sales;
    protected $personnelId = 'personnelId', $salesId = 'salesId';
    protected $service;
    //
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
        $this->salesRepository->expects($this->any())
                ->method('aSalesBelongToPersonnel')
                ->with($this->personnelId, $this->salesId)
                ->willReturn($this->sales);
        $this->service->execute($this->personnelId, $this->salesId, $this->task, $this->payload);
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
