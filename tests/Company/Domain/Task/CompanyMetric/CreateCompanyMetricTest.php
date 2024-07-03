<?php

namespace Company\Domain\Task\CompanyMetric;

use Company\Domain\Model\CompanyMetricData;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\MetricType;
use Shared\Domain\Enum\RecurrenceType;
use Tests\Company\Domain\Task\TaskInCompanyTestBase;

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
