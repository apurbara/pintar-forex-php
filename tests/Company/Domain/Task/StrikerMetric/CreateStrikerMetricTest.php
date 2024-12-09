<?php

namespace Company\Domain\Task\StrikerMetric;

use Company\Domain\Model\StrikerMetricData;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\RecurrenceType;
use Shared\Domain\Enum\SalesMetricType;
use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class CreateStrikerMetricTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareStrikerMetricDependency();
        
        $this->task = new CreateStrikerMetric($this->strikerMetricRepository);
        $this->payload = (new StrikerMetricData())
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
        $this->strikerMetricRepository->expects($this->once())
                ->method('nextIdentity')
                ->willReturn($this->strikerMetricId);
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_setPayloadId()
    {
        $this->execute();
        $this->assertSame($this->strikerMetricId, $this->payload->id);
    }
    public function test_execute_addStrikerMetricToRepository()
    {
        $this->strikerMetricRepository->expects($this->once())
                ->method('add');
        $this->execute();
    }
}
