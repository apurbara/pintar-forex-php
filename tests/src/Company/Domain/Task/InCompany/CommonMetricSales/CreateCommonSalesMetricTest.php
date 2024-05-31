<?php

namespace Company\Domain\Task\InCompany\CommonSalesMetric;

use Company\Domain\Model\CommonSalesMetricData;
use SharedContext\Domain\Enum\EvaluationType;
use SharedContext\Domain\Enum\MetricType;
use SharedContext\Domain\Enum\RecurrenceType;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

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
