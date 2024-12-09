<?php

namespace Company\Domain\Task\FactFinderMetric;

use Company\Domain\Model\FactFinderMetricData;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\RecurrenceType;
use Shared\Domain\Enum\SalesMetricType;
use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class CreateFactFinderMetricTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareFactFinderMetricDependency();
        
        $this->task = new CreateFactFinderMetric($this->factFinderMetricRepository);
        $this->payload = (new FactFinderMetricData())
                ->setName('name')
                ->setTarget(999)
                ->setDailyReminderTarget(33)
                ->setEvaluationType(EvaluationType::SUM->value)
                ->setRecurrenceCount(12)
                ->setRecurrenceType(RecurrenceType::MONTHLY->value)
                ->setSalesMetricType(SalesMetricType::SUCCESSFULL_ASSIGNMENT->value);
    }
    
    //
    protected function execute()
    {
        $this->factFinderMetricRepository->expects($this->once())
                ->method('nextIdentity')
                ->willReturn($this->factFinderMetricId);
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_setPayloadId()
    {
        $this->execute();
        $this->assertSame($this->factFinderMetricId, $this->payload->id);
    }
    public function test_execute_addFactFinderMetricToRepository()
    {
        $this->factFinderMetricRepository->expects($this->once())
                ->method('add');
        $this->execute();
    }
}
