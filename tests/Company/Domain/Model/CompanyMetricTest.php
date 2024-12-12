<?php

namespace Company\Domain\Model;

use DateTimeImmutable;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\MetricType;
use Shared\Domain\Enum\RecurrenceType;
use Tests\TestBase;

class CompanyMetricTest extends TestBase
{
    protected $companyMetric;
    protected $id = 'newId', $displaySchema = 'new display schema', $evaluationType, $metricType, $name = 'new name', $recurrenceCount = 6, $recurrenceType, $target = 9999;
    
    protected function setUp(): void
    {
        parent::setUp();
        $data = (new CompanyMetricData())
                ->setDisplaySchema('display schema')
                ->setEvaluationType(EvaluationType::COUNT->value)
                ->setMetricType(MetricType::APPROVED_CLOSING_REQUEST->value)
                ->setName('name')
                ->setRecurrenceCount(3)
                ->setRecurrenceType(RecurrenceType::YEARLY->value)
                ->setTarget(1000);
        $this->companyMetric = new TestableCompanyMetric('id', $data);
        $this->companyMetric->lastModifiedTime = new \DateTimeImmutable('-1 months');
        //
        $this->evaluationType = EvaluationType::SUM->value;
        $this->metricType = MetricType::GREETING_ACTIVITY_REPORT->value;
        $this->recurrenceType = RecurrenceType::MONTHLY->value;
    }
    
    //
    protected function createData()
    {
        return (new CompanyMetricData())
            ->setDisplaySchema($this->displaySchema)
            ->setEvaluationType($this->evaluationType)
            ->setMetricType($this->metricType)
            ->setName($this->name)
            ->setRecurrenceCount($this->recurrenceCount)
            ->setRecurrenceType($this->recurrenceType)
            ->setTarget($this->target);
    }
    
    //
    protected function construct()
    {
        return new TestableCompanyMetric($this->id, $this->createData());
    }
    public function test_construct_setProperties()
    {
        $companyMetric = $this->construct();
        $this->assertSame($this->id, $companyMetric->id);
        $this->assertFalse($companyMetric->disabled);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($companyMetric->createdTime);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($companyMetric->lastModifiedTime);
        $this->assertSame($this->name, $companyMetric->name);
        $this->assertSame($this->target, $companyMetric->target);
        $this->assertSame(MetricType::from($this->metricType), $companyMetric->metricType);
        $this->assertSame(EvaluationType::from($this->evaluationType), $companyMetric->evaluationType);
        $this->assertSame(RecurrenceType::from($this->recurrenceType), $companyMetric->recurrenceType);
        $this->assertSame($this->recurrenceCount, $companyMetric->recurrenceCount);
        $this->assertSame($this->displaySchema, $companyMetric->displaySchema);
    }
    public function test_construct_emptyName_forbidden()
    {
        $this->name = '';
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'name is mandatory');
    }
    
    //
    protected function update()
    {
        $this->companyMetric->update($this->createData());
    }
    public function test_update_setProperties()
    {
        $this->update();
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($this->companyMetric->lastModifiedTime);
        $this->assertSame($this->name, $this->companyMetric->name);
        $this->assertSame($this->target, $this->companyMetric->target);
        $this->assertSame(MetricType::from($this->metricType), $this->companyMetric->metricType);
        $this->assertSame(EvaluationType::from($this->evaluationType), $this->companyMetric->evaluationType);
        $this->assertSame(RecurrenceType::from($this->recurrenceType), $this->companyMetric->recurrenceType);
        $this->assertSame($this->recurrenceCount, $this->companyMetric->recurrenceCount);
        $this->assertSame($this->displaySchema, $this->companyMetric->displaySchema);
    }
    public function test_update_emptyName_forbidden()
    {
        $this->name = '';
        $this->assertRegularExceptionThrowed(fn() => $this->update(), 'Bad Request', 'name is mandatory');
    }
    
    //
    protected function disable()
    {
        $this->companyMetric->disable();
    }
    public function test_disable_setDisabled()
    {
        $this->disable();
        $this->assertTrue($this->companyMetric->disabled);
    }
    
    //
    protected function enable()
    {
        $this->companyMetric->enable();
    }
    public function test_enable_setDisabledFalse()
    {
        $this->companyMetric->disabled = true;
        $this->enable();
        $this->assertFalse($this->companyMetric->disabled);
    }
}

class TestableCompanyMetric extends CompanyMetric
{
    public string $id;
    public bool $disabled;
    public DateTimeImmutable $createdTime;
    public DateTimeImmutable $lastModifiedTime;
    public string $name;
    public int $target;
    public MetricType $metricType;
    public EvaluationType $evaluationType;
    public RecurrenceType $recurrenceType;
    public ?int $recurrenceCount;
    public ?string $displaySchema;
}
