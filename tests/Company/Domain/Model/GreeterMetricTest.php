<?php

namespace Company\Domain\Model;

use DateTimeImmutable;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\RecurrenceType;
use Shared\Domain\Enum\SalesMetricType;
use Tests\TestBase;

class GreeterMetricTest extends TestBase
{
    protected $greeterMetric;
    protected $id = 'newId', $monthlyTarget = 2250, $dailyReminderTarget = 75, $salesMetricType, $evaluationType, $recurrenceType, $recurrenceCount = 3;
    
    protected function setUp(): void
    {
        parent::setUp();
        $data = (new GreeterMetricData())
                ->setMonthlyTarget(999)
                ->setDailyReminderTarget(33)
                ->setSalesMetricType(SalesMetricType::SALES_ACTIVITY->value)
                ->setEvaluationType(EvaluationType::AVG->value)
                ->setRecurrenceType(RecurrenceType::WEEKLY->value)
                ->setRecurrenceCount(6);
        $this->greeterMetric = new TestableGreeterMetric('id', $data);
        //
        $this->salesMetricType = SalesMetricType::SUCCESSFULL_ASSIGNMENT->value;
        $this->evaluationType = EvaluationType::SUM->value;
        $this->recurrenceType = RecurrenceType::MONTHLY->value;
    }
    
    //
    protected function createData()
    {
        return (new GreeterMetricData())
                ->setMonthlyTarget($this->monthlyTarget)
                ->setDailyReminderTarget($this->dailyReminderTarget)
                ->setSalesMetricType($this->salesMetricType)
                ->setEvaluationType($this->evaluationType)
                ->setRecurrenceType($this->recurrenceType)
                ->setRecurrenceCount($this->recurrenceCount);
    }
    
    //
    protected function construct()
    {
        return new TestableGreeterMetric($this->id, $this->createData());
    }
    public function test_construct_setProperties()
    {
        $greeterMetric = $this->construct();
        $this->assertSame($this->id, $greeterMetric->id);
        $this->assertFalse($greeterMetric->disabled);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($greeterMetric->createdTime);
        $this->assertSame($this->monthlyTarget, $greeterMetric->monthlyTarget);
        $this->assertSame($this->dailyReminderTarget, $greeterMetric->dailyReminderTarget);
        $this->assertSame(SalesMetricType::from($this->salesMetricType), $greeterMetric->salesMetricType);
        $this->assertSame(EvaluationType::from($this->evaluationType), $greeterMetric->evaluationType);
        $this->assertSame(RecurrenceType::from($this->recurrenceType), $greeterMetric->recurrenceType);
        $this->assertSame($this->recurrenceCount, $greeterMetric->recurrenceCount);
    }
    
    //
    protected function update()
    {
        $this->greeterMetric->update($this->createData());
    }
    public function test_update_updateProperties()
    {
        $this->update();
        $this->assertSame($this->monthlyTarget, $this->greeterMetric->monthlyTarget);
        $this->assertSame($this->dailyReminderTarget, $this->greeterMetric->dailyReminderTarget);
        $this->assertSame(SalesMetricType::from($this->salesMetricType), $this->greeterMetric->salesMetricType);
        $this->assertSame(EvaluationType::from($this->evaluationType), $this->greeterMetric->evaluationType);
        $this->assertSame(RecurrenceType::from($this->recurrenceType), $this->greeterMetric->recurrenceType);
        $this->assertSame($this->recurrenceCount, $this->greeterMetric->recurrenceCount);
    }
    
    //
    protected function disable()
    {
        $this->greeterMetric->disable();
    }
    public function test_disable_setDisabled()
    {
        $this->disable();
        $this->assertTrue($this->greeterMetric->disabled);
    }
    
    //
    protected function enable()
    {
        $this->greeterMetric->enable();
    }
    public function test_enable_setDisabledFalse()
    {
        $this->greeterMetric->disabled = true;
        $this->enable();
        $this->assertFalse($this->greeterMetric->disabled);
    }
}

class TestableGreeterMetric extends GreeterMetric{
    public string $id;
    public bool $disabled;
    public DateTimeImmutable $createdTime;
    public int $monthlyTarget;
    public ?int $dailyReminderTarget;
    public SalesMetricType $salesMetricType;
    public EvaluationType $evaluationType;
    public RecurrenceType $recurrenceType;
    public ?int $recurrenceCount;
}
