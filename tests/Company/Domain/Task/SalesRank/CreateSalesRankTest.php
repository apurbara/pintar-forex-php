<?php

namespace Company\Domain\Task\SalesRank;

use Company\Domain\Model\SalesRankData;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\MetricType;
use Shared\Domain\Enum\QueryOrder;
use Shared\Domain\Enum\RecurrenceType;
use Tests\Company\Domain\Task\TaskInCompanyTestBase;

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
                ->setSalesMetricType(MetricType::APPROVED_CLOSING_REQUEST->value)
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
