<?php

namespace Company\Domain\Task\GreeterMetric;

use Company\Domain\Model\GreeterMetricData;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\RecurrenceType;
use Shared\Domain\Enum\SalesMetricType;
use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class CreateGreeterMetricTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareGreeterMetricDependency();
        
        $this->task = new CreateGreeterMetric($this->greeterMetricRepository);
        $this->payload = (new GreeterMetricData())
                ->setMonthlyTarget(999)
                ->setDailyReminderTarget(33)
                ->setEvaluationType(EvaluationType::SUM->value)
                ->setRecurrenceCount(12)
                ->setRecurrenceType(RecurrenceType::MONTHLY->value)
                ->setSalesMetricType(SalesMetricType::SUCCESSFULL_ASSIGNMENT->value);
    }
    
    //
    protected function execute()
    {
        $this->greeterMetricRepository->expects($this->once())
                ->method('nextIdentity')
                ->willReturn($this->greeterMetricId);
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_setPayloadId()
    {
        $this->execute();
        $this->assertSame($this->greeterMetricId, $this->payload->id);
    }
    public function test_execute_addGreeterMetricToRepository()
    {
        $this->greeterMetricRepository->expects($this->once())
                ->method('add');
        $this->execute();
    }
}
