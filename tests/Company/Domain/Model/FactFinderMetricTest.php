<?php

namespace Company\Domain\Model;

use DateTimeImmutable;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\RecurrenceType;
use Shared\Domain\Enum\SalesMetricType;
use Tests\TestBase;

class FactFinderMetricTest extends TestBase
{
    protected $factFinderMetric;
    protected $id = 'newId', $name = 'newName', $target = 2250, $dailyReminderTarget = 75, $salesMetricType, $evaluationType, $recurrenceType, $recurrenceCount = 3;
    
    protected function setUp(): void
    {
        parent::setUp();
        $data = (new FactFinderMetricData())
                ->setName('name')
                ->setTarget(999)
                ->setDailyReminderTarget(33)
                ->setSalesMetricType(SalesMetricType::SALES_ACTIVITY->value)
                ->setEvaluationType(EvaluationType::AVG->value)
                ->setRecurrenceType(RecurrenceType::WEEKLY->value)
                ->setRecurrenceCount(6);
        $this->factFinderMetric = new TestableFactFinderMetric('id', $data);
        //
        $this->salesMetricType = SalesMetricType::SUCCESSFULL_ASSIGNMENT->value;
        $this->evaluationType = EvaluationType::SUM->value;
        $this->recurrenceType = RecurrenceType::MONTHLY->value;
    }
    
    //
    protected function createData()
    {
        return (new FactFinderMetricData())
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
        return new TestableFactFinderMetric($this->id, $this->createData());
    }
    public function test_construct_setProperties()
    {
        $factFinderMetric = $this->construct();
        $this->assertSame($this->id, $factFinderMetric->id);
        $this->assertFalse($factFinderMetric->disabled);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($factFinderMetric->createdTime);
        $this->assertSame($this->name, $factFinderMetric->name);
        $this->assertSame($this->target, $factFinderMetric->target);
        $this->assertSame($this->dailyReminderTarget, $factFinderMetric->dailyReminderTarget);
        $this->assertSame(SalesMetricType::from($this->salesMetricType), $factFinderMetric->salesMetricType);
        $this->assertSame(EvaluationType::from($this->evaluationType), $factFinderMetric->evaluationType);
        $this->assertSame(RecurrenceType::from($this->recurrenceType), $factFinderMetric->recurrenceType);
        $this->assertSame($this->recurrenceCount, $factFinderMetric->recurrenceCount);
    }
    public function test_construct_emptyName_badRequest()
    {
        $this->name = '';
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'name is mandatory');
    }
    
    //
    protected function update()
    {
        $this->factFinderMetric->update($this->createData());
    }
    public function test_update_updateProperties()
    {
        $this->update();
        $this->assertSame($this->name, $this->factFinderMetric->name);
        $this->assertSame($this->target, $this->factFinderMetric->target);
        $this->assertSame($this->dailyReminderTarget, $this->factFinderMetric->dailyReminderTarget);
        $this->assertSame(SalesMetricType::from($this->salesMetricType), $this->factFinderMetric->salesMetricType);
        $this->assertSame(EvaluationType::from($this->evaluationType), $this->factFinderMetric->evaluationType);
        $this->assertSame(RecurrenceType::from($this->recurrenceType), $this->factFinderMetric->recurrenceType);
        $this->assertSame($this->recurrenceCount, $this->factFinderMetric->recurrenceCount);
    }
    public function test_update_emptyName_badRequest()
    {
        $this->name = '';
        $this->assertRegularExceptionThrowed(fn() => $this->update(), 'Bad Request', 'name is mandatory');
    }
    
    //
    protected function disable()
    {
        $this->factFinderMetric->disable();
    }
    public function test_disable_setDisabled()
    {
        $this->disable();
        $this->assertTrue($this->factFinderMetric->disabled);
    }
    
    //
    protected function enable()
    {
        $this->factFinderMetric->enable();
    }
    public function test_enable_setDisabledFalse()
    {
        $this->factFinderMetric->disabled = true;
        $this->enable();
        $this->assertFalse($this->factFinderMetric->disabled);
    }
}

class TestableFactFinderMetric extends FactFinderMetric{
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
