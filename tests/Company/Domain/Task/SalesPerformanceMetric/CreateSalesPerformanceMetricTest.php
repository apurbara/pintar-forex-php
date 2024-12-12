<?php

namespace Company\Domain\Task\SalesPerformanceMetric;

use Company\Domain\Model\SalesPerformanceMetric\SalesPerformanceMetricEvaluationData;
use Company\Domain\Model\SalesPerformanceMetricData;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\RecurrenceType;
use Shared\Domain\Enum\SalesPerformanceMetricType;
use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class CreateSalesPerformanceMetricTest extends TaskInCompanyTestBase
{

    protected $task;
    protected $payload;

    protected function setUp(): void
    {
        $this->prepareSalesPerformanceMetricDependency();
        //
        $this->task = new CreateSalesPerformanceMetric($this->salesPerformanceMetricRepository);
        $evaluationData = (new SalesPerformanceMetricEvaluationData())
                ->setAlias('alias')
                ->setEvaluationType(EvaluationType::AVG->value);
        $this->payload = (new SalesPerformanceMetricData())
                ->setDisplaySchema('display schema')
                ->setSalesPerformanceMetricType(SalesPerformanceMetricType::APPROVED_CLOSING_REQUEST_COUNT->value)
                ->setName('name')
                ->setRecurrenceCount(4)
                ->setRecurrenceType(RecurrenceType::DAILY->value)
                ->addEvaluationData($evaluationData);
    }
    
    //
    protected function execute()
    {
        $this->salesPerformanceMetricRepository->expects($this->once())
                ->method('nextIdentity')
                ->willReturn($this->salesPerformanceMetricId);
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_setPayloadId()
    {
        $this->execute();
        $this->assertSame($this->salesPerformanceMetricId, $this->payload->id);
    }
    public function test_execute_addSalesPerformanceMetricToRepository()
    {
        $this->salesPerformanceMetricRepository->expects($this->once())
                ->method('add');
        $this->execute();
    }
}
