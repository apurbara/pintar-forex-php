<?php

namespace Company\Domain\Task\CommonSalesMetric;

use Company\Domain\Model\CommonSalesMetricData;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\MetricType;
use Shared\Domain\Enum\RecurrenceType;
use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class CreateCommonSalesMetricTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCommonSalesMetricDependency();
        //
        $this->task = new CreateCommonSalesMetric($this->commonSalesMetricRepository);
        $this->payload = (new CommonSalesMetricData())
                ->setDisplaySchema('display schema')
                ->setEvaluationType(EvaluationType::AVG->value)
                ->setMetricType(MetricType::APPROVED_CLOSING_REQUEST->value)
                ->setName('name')
                ->setRecurrenceCount(3)
                ->setRecurrenceType(RecurrenceType::DAILY->value)
                ->setTarget(200);
    }
    
    //
    protected function execute()
    {
        $this->commonSalesMetricRepository->expects($this->once())
                ->method('nextIdentity')
                ->willReturn($this->commonSalesMetricId);
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_setPayloadId()
    {
        $this->execute();
        $this->assertSame($this->commonSalesMetricId, $this->payload->id);
    }
    public function test_execute_addCommonMetricSalesToRepository()
    {
        $this->commonSalesMetricRepository->expects($this->once())
                ->method('add');
        $this->execute();
    }
}
