<?php

namespace Company\Domain\Task\InCompany\CompanyMetric;

use Company\Domain\Model\CompanyMetricData;
use SharedContext\Domain\Enum\EvaluationType;
use SharedContext\Domain\Enum\MetricType;
use SharedContext\Domain\Enum\RecurrenceType;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class CreateCompanyMetricTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCompanyMetricDependency();
        $this->task = new CreateCompanyMetric($this->companyMetricRepository);
        $this->payload = (new CompanyMetricData())
                ->setDisplaySchema('display schema')
                ->setEvaluationType(EvaluationType::SUM->value)
                ->setMetricType(MetricType::APPROVED_CLOSING_REQUEST->value)
                ->setName('name')
                ->setRecurrenceCount(3)
                ->setRecurrenceType(RecurrenceType::MONTHLY->value)
                ->setTarget(2000);
    }
    
    //
    protected function execute()
    {
        $this->companyMetricRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->companyMetricId);
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_setPayloadId()
    {
        $this->execute();
        $this->assertSame($this->companyMetricId, $this->payload->id);
    }
    public function test_execute_addCompanyMetricToRepository()
    {
        $this->companyMetricRepository->expects($this->once())
                ->method('add');
        $this->execute();
    }
}
