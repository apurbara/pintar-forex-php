<?php

namespace Company\Domain\Task\InCompany\SalesRank;

use Company\Domain\Model\SalesRankData;
use SharedContext\Domain\Enum\EvaluationType;
use SharedContext\Domain\Enum\MetricType;
use SharedContext\Domain\Enum\QueryOrder;
use SharedContext\Domain\Enum\RecurrenceType;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class CreateSalesRankTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesRankDependency();
        //
        $this->task = new CreateSalesRank($this->salesRankRepository);
        $this->payload = (new SalesRankData())
                ->setDisplaySalesNumber(5)
                ->setDisplaySchema('display schema')
                ->setEvaluationType(EvaluationType::COUNT->value)
                ->setMetricType(MetricType::APPROVED_CLOSING_REQUEST->value)
                ->setName('name')
                ->setOrder(QueryOrder::ASC->value)
                ->setRecurrenceType(RecurrenceType::DAILY->value);
    }
    
    //
    protected function execute()
    {
        $this->salesRankRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->salesRankId);
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_setPayloadId()
    {
        $this->execute();
        $this->assertSame($this->salesRankId, $this->payload->id);
    }
    public function test_execute_addSalesRankToRepository()
    {
        $this->salesRankRepository->expects($this->once())
                ->method('add');
        $this->execute();
    }
}
