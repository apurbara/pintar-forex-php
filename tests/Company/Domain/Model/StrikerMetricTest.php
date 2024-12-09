<?php

namespace Company\Domain\Model;

use DateTimeImmutable;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\RecurrenceType;
use Shared\Domain\Enum\SalesMetricType;
use Tests\TestBase;

class StrikerMetricTest extends TestBase
{
    protected $strikerMetric;
    protected $id = 'newId', $name = 'newName', $target = 2250, $dailyReminderTarget = 75, $salesMetricType, $evaluationType, $recurrenceType, $recurrenceCount = 3;
    
    protected function setUp(): void
    {
        parent::setUp();
        $data = (new StrikerMetricData())
                ->setName('name')
                ->setTarget(999)
                ->setDailyReminderTarget(33)
                ->setSalesMetricType(SalesMetricType::SALES_ACTIVITY->value)
                ->setEvaluationType(EvaluationType::AVG->value)
                ->setRecurrenceType(RecurrenceType::WEEKLY->value)
                ->setRecurrenceCount(6);
        $this->strikerMetric = new TestableStrikerMetric('id', $data);
        //
        $this->salesMetricType = SalesMetricType::SUCCESSFULL_ASSIGNMENT->value;
        $this->evaluationType = EvaluationType::SUM->value;
        $this->recurrenceType = RecurrenceType::MONTHLY->value;
    }
    
    //
    protected function createData()
    {
        return (new StrikerMetricData())
                ->setName($this->name)
                ->setTarget($this->target)
                ->setDailyReminderTarget($this->dailyReminderTarget)
                ->setSalesMetricType($this->salesMetricType)
                ->setEvaluationType($this->evaluationType)
                ->setRecurrenceType($this->recurrenceType)
                ->setRecurrenceCount($this->recurrenceCount);
    }
    
    //
    protected function construct()
    {
        return new TestableStrikerMetric($this->id, $this->createData());
    }
    public function test_construct_setProperties()
    {
        $strikerMetric = $this->construct();
        $this->assertSame($this->id, $strikerMetric->id);
        $this->assertFalse($strikerMetric->disabled);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($strikerMetric->createdTime);
        $this->assertSame($this->name, $strikerMetric->name);
        $this->assertSame($this->target, $strikerMetric->target);
        $this->assertSame($this->dailyReminderTarget, $strikerMetric->dailyReminderTarget);
        $this->assertSame(SalesMetricType::from($this->salesMetricType), $strikerMetric->salesMetricType);
        $this->assertSame(EvaluationType::from($this->evaluationType), $strikerMetric->evaluationType);
        $this->assertSame(RecurrenceType::from($this->recurrenceType), $strikerMetric->recurrenceType);
        $this->assertSame($this->recurrenceCount, $strikerMetric->recurrenceCount);
    }
    public function test_construct_emptyName_badRequest()
    {
        $this->name = '';
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'name is mandatory');
    }
    
    //
    protected function update()
    {
        $this->strikerMetric->update($this->createData());
    }
    public function test_update_updateProperties()
    {
        $this->update();
        $this->assertSame($this->name, $this->strikerMetric->name);
        $this->assertSame($this->target, $this->strikerMetric->target);
        $this->assertSame($this->dailyReminderTarget, $this->strikerMetric->dailyReminderTarget);
        $this->assertSame(SalesMetricType::from($this->salesMetricType), $this->strikerMetric->salesMetricType);
        $this->assertSame(EvaluationType::from($this->evaluationType), $this->strikerMetric->evaluationType);
        $this->assertSame(RecurrenceType::from($this->recurrenceType), $this->strikerMetric->recurrenceType);
        $this->assertSame($this->recurrenceCount, $this->strikerMetric->recurrenceCount);
    }
    public function test_update_emptyName_badRequest()
    {
        $this->name = '';
        $this->assertRegularExceptionThrowed(fn() => $this->update(), 'Bad Request', 'name is mandatory');
    }
    
    //
    protected function disable()
    {
        $this->strikerMetric->disable();
    }
    public function test_disable_setDisabled()
    {
        $this->disable();
        $this->assertTrue($this->strikerMetric->disabled);
    }
    
    //
    protected function enable()
    {
        $this->strikerMetric->enable();
    }
    public function test_enable_setDisabledFalse()
    {
        $this->strikerMetric->disabled = true;
        $this->enable();
        $this->assertFalse($this->strikerMetric->disabled);
    }
}

class TestableStrikerMetric extends StrikerMetric{
    public string $id;
    public bool $disabled;
    public DateTimeImmutable $createdTime;
    public string $name;
    public int $target;
    public ?int $dailyReminderTarget;
    public SalesMetricType $salesMetricType;
    public EvaluationType $evaluationType;
    public RecurrenceType $recurrenceType;
    public ?int $recurrenceCount;
}
