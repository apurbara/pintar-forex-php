<?php

namespace Company\Domain\Model;

use DateTimeImmutable;
use SharedContext\Domain\Enum\EvaluationType;
use SharedContext\Domain\Enum\MetricType;
use SharedContext\Domain\Enum\RecurrenceType;
use Tests\TestBase;

class CommonSalesMetricTest extends TestBase
{
    protected $commonSalesMetric;
    protected $id = 'newId', $displaySchema = 'new display schema', $evaluationType, $metricType, $name = 'new name', $recurrenceCount = 6, $recurrenceType, $target = 9999;
    
    protected function setUp(): void
    {
        parent::setUp();
        $data = (new CommonSalesMetricData())
                ->setDisplaySchema('display schema')
                ->setEvaluationType(EvaluationType::COUNT->value)
                ->setMetricType(MetricType::APPROVED_CLOSING_REQUEST->value)
                ->setName('name')
                ->setRecurrenceCount(3)
                ->setRecurrenceType(RecurrenceType::YEARLY->value)
                ->setTarget(1000);
        $this->commonSalesMetric = new TestableCommonSalesMetric('id', $data);
        $this->commonSalesMetric->lastModifiedTime = new \DateTimeImmutable('-1 months');
        //
        $this->evaluationType = EvaluationType::SUM->value;
        $this->metricType = MetricType::SALES_ACTIVITY_REPORT->value;
        $this->recurrenceType = RecurrenceType::MONTHLY->value;
    }
    
    //
    protected function createData()
    {
        return (new CommonSalesMetricData())
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
        return new TestableCommonSalesMetric($this->id, $this->createData());
    }
    public function test_construct_setProperties()
    {
        $commonSalesMetric = $this->construct();
        $this->assertSame($this->id, $commonSalesMetric->id);
        $this->assertFalse($commonSalesMetric->disabled);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($commonSalesMetric->createdTime);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($commonSalesMetric->lastModifiedTime);
        $this->assertSame($this->name, $commonSalesMetric->name);
        $this->assertSame($this->target, $commonSalesMetric->target);
        $this->assertSame(MetricType::from($this->metricType), $commonSalesMetric->metricType);
        $this->assertSame(EvaluationType::from($this->evaluationType), $commonSalesMetric->evaluationType);
        $this->assertSame(RecurrenceType::from($this->recurrenceType), $commonSalesMetric->recurrenceType);
        $this->assertSame($this->recurrenceCount, $commonSalesMetric->recurrenceCount);
        $this->assertSame($this->displaySchema, $commonSalesMetric->displaySchema);
    }
    public function test_construct_emptyName_forbidden()
    {
        $this->name = '';
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'name is mandatory');
    }
    
    //
    protected function update()
    {
        $this->commonSalesMetric->update($this->createData());
    }
    public function test_update_setProperties()
    {
        $this->update();
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($this->commonSalesMetric->lastModifiedTime);
        $this->assertSame($this->name, $this->commonSalesMetric->name);
        $this->assertSame($this->target, $this->commonSalesMetric->target);
        $this->assertSame(MetricType::from($this->metricType), $this->commonSalesMetric->metricType);
        $this->assertSame(EvaluationType::from($this->evaluationType), $this->commonSalesMetric->evaluationType);
        $this->assertSame(RecurrenceType::from($this->recurrenceType), $this->commonSalesMetric->recurrenceType);
        $this->assertSame($this->recurrenceCount, $this->commonSalesMetric->recurrenceCount);
        $this->assertSame($this->displaySchema, $this->commonSalesMetric->displaySchema);
    }
    public function test_update_emptyName_forbidden()
    {
        $this->name = '';
        $this->assertRegularExceptionThrowed(fn() => $this->update(), 'Bad Request', 'name is mandatory');
    }
    
    //
    protected function disable()
    {
        $this->commonSalesMetric->disable();
    }
    public function test_disable_setDisabled()
    {
        $this->disable();
        $this->assertTrue($this->commonSalesMetric->disabled);
    }
    
    //
    protected function enable()
    {
        $this->commonSalesMetric->enable();
    }
    public function test_enable_setDisabledFalse()
    {
        $this->commonSalesMetric->disabled = true;
        $this->enable();
        $this->assertFalse($this->commonSalesMetric->disabled);
    }
}

class TestableCommonSalesMetric extends CommonSalesMetric
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
