<?php

namespace Company\Domain\Model;

use DateTimeImmutable;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\MetricType;
use Shared\Domain\Enum\QueryOrder;
use Shared\Domain\Enum\RecurrenceType;
use Tests\TestBase;

class SalesRankTest extends TestBase
{

    protected $salesRank;
    protected $id = 'newId', $name = 'new name', $metricType, $evaluationType, $recurrenceType,
            $displaySalesNumber = 5, $order, $displaySchema = 'new display schema';

    protected function setUp(): void
    {
        parent::setUp();
        $data = (new SalesRankData())
                ->setDisplaySalesNumber(3)
                ->setDisplaySchema('display schema')
                ->setEvaluationType(EvaluationType::AVG->value)
                ->setMetricType(MetricType::APPROVED_CLOSING_REQUEST->value)
                ->setName('name')
                ->setOrder(QueryOrder::ASC->value)
                ->setRecurrenceType(RecurrenceType::DAILY->value);
        $this->salesRank = new TestableSalesRank('id', $data);
        $this->salesRank->lastModifiedTime = new DateTimeImmutable('-1 months');
        //
        $this->metricType = MetricType::SALES_ACTIVITY_REPORT->value;
        $this->evaluationType = EvaluationType::COUNT->value;
        $this->recurrenceType = RecurrenceType::MONTHLY->value;
        $this->order = QueryOrder::DESC->value;
    }

    //
    protected function createData()
    {
        return (new SalesRankData())
                        ->setDisplaySalesNumber($this->displaySalesNumber)
                        ->setDisplaySchema($this->displaySchema)
                        ->setEvaluationType($this->evaluationType)
                        ->setMetricType($this->metricType)
                        ->setName($this->name)
                        ->setOrder($this->order)
                        ->setRecurrenceType($this->recurrenceType);
    }
    
    //
    protected function construct()
    {
        return new TestableSalesRank($this->id, $this->createData());
    }
    public function test_construct_setProperties()
    {
        $salesRank = $this->construct();
        $this->assertSame($this->id, $salesRank->id);
        $this->assertFalse($salesRank->disabled);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($salesRank->createdTime);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($salesRank->lastModifiedTime);
        $this->assertSame($this->name, $salesRank->name);
        $this->assertSame(MetricType::from($this->metricType), $salesRank->metricType);
        $this->assertSame(EvaluationType::from($this->evaluationType), $salesRank->evaluationType);
        $this->assertSame(RecurrenceType::from($this->recurrenceType), $salesRank->recurrenceType);
        $this->assertSame($this->displaySalesNumber, $salesRank->displaySalesNumber);
        $this->assertSame(QueryOrder::from($this->order), $salesRank->queryOrder);
        $this->assertSame($this->displaySchema, $salesRank->displaySchema);
    }
    public function test_construct_emptyName_badRequest()
    {
        $this->name = '';
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'name is mandatory');
    }
    public function test_construct_emptyDisplaySalesNumber_badRequest()
    {
        $this->displaySalesNumber = 0;
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'display sales number is mandatory');
    }
    
    //
    protected function update()
    {
        $this->salesRank->update($this->createData());
    }
    public function test_update_setProperties()
    {
        $this->update();
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($this->salesRank->lastModifiedTime);
        $this->assertSame($this->name, $this->salesRank->name);
        $this->assertSame(MetricType::from($this->metricType), $this->salesRank->metricType);
        $this->assertSame(EvaluationType::from($this->evaluationType), $this->salesRank->evaluationType);
        $this->assertSame(RecurrenceType::from($this->recurrenceType), $this->salesRank->recurrenceType);
        $this->assertSame($this->displaySalesNumber, $this->salesRank->displaySalesNumber);
        $this->assertSame(QueryOrder::from($this->order), $this->salesRank->queryOrder);
        $this->assertSame($this->displaySchema, $this->salesRank->displaySchema);
    }
    
    //
    protected function disable()
    {
        $this->salesRank->disable();
    }
    public function test_disable_setDisabled()
    {
        $this->disable();
        $this->assertTrue($this->salesRank->disabled);
    }
    
    //
    protected function enable()
    {
        $this->salesRank->enable();
    }
    public function test_enable_setDisabledFalse()
    {
        $this->salesRank->disabled = true;
        $this->enable();
        $this->assertFalse($this->salesRank->disabled);
    }
}

class TestableSalesRank extends SalesRank
{

    public string $id;
    public bool $disabled;
    public DateTimeImmutable $createdTime;
    public DateTimeImmutable $lastModifiedTime;
    public string $name;
    public MetricType $metricType;
    public EvaluationType $evaluationType;
    public RecurrenceType $recurrenceType;
    public int $displaySalesNumber;
    public QueryOrder $queryOrder;
    public ?string $displaySchema;
}
