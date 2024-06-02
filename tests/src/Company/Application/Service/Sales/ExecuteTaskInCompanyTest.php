<?php

namespace Company\Application\Service\Sales;

use Company\Domain\Model\Sales;
use Company\Domain\Model\SalesTaskInCompany;
use Tests\TestBase;

class ExecuteTaskInCompanyTest extends TestBase
{
    protected $salesRepository, $sales, $salesId = 'salesId';
    protected $service;
    protected $payload = 'task payload', $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->salesRepository = $this->buildMockOfInterface(SalesRepository::class);
        $this->sales = $this->buildMockOfClass(Sales::class);
        $this->service = new ExecuteTaskInCompany($this->salesRepository);
        $this->task = $this->buildMockOfInterface(SalesTaskInCompany::class);
    }
    
    //
    protected function execute()
    {
        $this->salesRepository->expects($this->any())
                ->method('ofId')
                ->with($this->salesId)
                ->willReturn($this->sales);
        $this->service->execute($this->salesId, $this->task, $this->payload);
    }
    public function test_execute_salesExecuteTask()
    {
        $this->sales->expects($this->once())
                ->method('executeTaskInCompany')
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
