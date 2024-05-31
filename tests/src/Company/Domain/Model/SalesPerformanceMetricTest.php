<?php

namespace Company\Domain\Model;

use Company\Domain\Model\SalesPerformanceMetric\SalesPerformanceMetricEvaluation;
use Company\Domain\Model\SalesPerformanceMetric\SalesPerformanceMetricEvaluationData;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use SharedContext\Domain\Enum\EvaluationType;
use SharedContext\Domain\Enum\RecurrenceType;
use SharedContext\Domain\Enum\SalesPerformanceMetricType;
use Tests\TestBase;

class SalesPerformanceMetricTest extends TestBase
{
    protected $salesPerformanceMetric;
    protected $salesPerformanceMetricEvaluation;
    //
    protected $id = 'newId', $displaySchema = 'new display schema', $metricType, $name = 'new name', 
            $recurrenceCount = 6, $recurrenceType, $target = 9999;
    protected $evaluationData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->evaluationData = (new SalesPerformanceMetricEvaluationData())
                ->setAlias('alias')
                ->setEvaluationType(EvaluationType::AVG->value);
        //
        $data = (new SalesPerformanceMetricData())
                ->setDisplaySchema('display schema')
                ->setMetricType(SalesPerformanceMetricType::APPROVED_CLOSING_REQUEST_COUNT->value)
                ->setName('name')
                ->setRecurrenceCount(3)
                ->setRecurrenceType(RecurrenceType::YEARLY->value);
        $data->addEvaluationData($this->evaluationData);
        
        $this->salesPerformanceMetric = new TestableSalesPerformanceMetric('id', $data);
        $this->salesPerformanceMetric->lastModifiedTime = new \DateTimeImmutable('-1 months');
        
        $this->salesPerformanceMetricEvaluation = $this->buildMockOfClass(SalesPerformanceMetricEvaluation::class);
        $this->salesPerformanceMetric->evaluations = new ArrayCollection();
        $this->salesPerformanceMetric->evaluations->add($this->salesPerformanceMetricEvaluation);
        //
        $this->metricType = SalesPerformanceMetricType::APPROVED_CLOSING_REQUEST_SUM->value;
        $this->recurrenceType = RecurrenceType::MONTHLY->value;
    }
    
    //
    protected function createData()
    {
        return (new SalesPerformanceMetricData())
                ->setDisplaySchema($this->displaySchema)
                ->setMetricType($this->metricType)
                ->setName($this->name)
                ->setRecurrenceCount($this->recurrenceCount)
                ->setRecurrenceType($this->recurrenceType)
                ->addEvaluationData($this->evaluationData);
    }
    
    //
    protected function construct()
    {
        return new TestableSalesPerformanceMetric($this->id, $this->createData());
    }
    public function test_construct_setProperties()
    {
        $salesPerformanceMetric = $this->construct();
        $this->assertSame($this->id, $salesPerformanceMetric->id);
        $this->assertFalse($salesPerformanceMetric->disabled);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($salesPerformanceMetric->createdTime);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($salesPerformanceMetric->lastModifiedTime);
        $this->assertSame($this->name, $salesPerformanceMetric->name);
        $this->assertSame(SalesPerformanceMetricType::from($this->metricType), $salesPerformanceMetric->metricType);
        $this->assertSame(RecurrenceType::from($this->recurrenceType), $salesPerformanceMetric->recurrenceType);
        $this->assertSame($this->recurrenceCount, $salesPerformanceMetric->recurrenceCount);
        $this->assertSame($this->displaySchema, $salesPerformanceMetric->displaySchema);
        //
        $this->assertInstanceOf(ArrayCollection::class, $salesPerformanceMetric->evaluations);
    }
    public function test_construct_emptyName_forbidden()
    {
        $this->name = '';
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'name is mandatory');
    }
    public function test_construct_addEvaluation()
    {
        $salesPerformanceMetric = $this->construct();
        $this->assertInstanceOf(SalesPerformanceMetricEvaluation::class, $salesPerformanceMetric->evaluations->first());
    }
    public function test_construct_noEvaluation_badRequest()
    {
        $data = $this->createData();
        $data->pullEvaluationDataAssociationWithType($this->evaluationData->evaluationType);
        $this->assertRegularExceptionThrowed(fn() => new TestableSalesPerformanceMetric($this->id, $data), 'Bad Request', 'at least one evaluation is required');
    }
    
    //
    protected function update()
    {
        $this->salesPerformanceMetric->update($this->createData());
    }
    public function test_update_setProperties()
    {
        $this->update();
        $this->assertSame($this->name, $this->salesPerformanceMetric->name);
        $this->assertSame(SalesPerformanceMetricType::from($this->metricType), $this->salesPerformanceMetric->metricType);
        $this->assertSame(RecurrenceType::from($this->recurrenceType), $this->salesPerformanceMetric->recurrenceType);
        $this->assertSame($this->recurrenceCount, $this->salesPerformanceMetric->recurrenceCount);
        $this->assertSame($this->displaySchema, $this->salesPerformanceMetric->displaySchema);
    }
    public function test_update_emptyName_forbidden()
    {
        $this->name = '';
        $this->assertRegularExceptionThrowed(fn() => $this->update(), 'Bad Request', 'name is mandatory');
    }
    public function test_update_updateExistingEvaluation()
    {
        $this->salesPerformanceMetricEvaluation->expects($this->once())
                ->method('update');
        $this->update();
    }
    public function test_update_addLefoverEvaluation()
    {
        $this->update();
        $this->assertEquals(2, $this->salesPerformanceMetric->evaluations->count());
        $this->assertInstanceOf(SalesPerformanceMetricEvaluation::class, $this->salesPerformanceMetric->evaluations->last());
    }
    public function test_update_noEvaluation_badRequest()
    {
        $this->salesPerformanceMetric->evaluations->clear();
        $data = $this->createData();
        $data->pullEvaluationDataAssociationWithType($this->evaluationData->evaluationType);
        $this->assertRegularExceptionThrowed(fn() => $this->salesPerformanceMetric->update($data), 'Bad Request', 'at least one evaluation is required');
    }
    public function test_update_noActiveEvaluation_badRequest()
    {
        $this->salesPerformanceMetricEvaluation->expects($this->any())
                ->method('isRemoved')
                ->willReturn(true);
        $data = $this->createData();
        $data->pullEvaluationDataAssociationWithType($this->evaluationData->evaluationType);
        $this->assertRegularExceptionThrowed(fn() => $this->salesPerformanceMetric->update($data), 'Bad Request', 'at least one evaluation is required');
    }
    
    //
    protected function disable()
    {
        $this->salesPerformanceMetric->disable();
    }
    public function test_disable_setDisabled()
    {
        $this->disable();
        $this->assertTrue($this->salesPerformanceMetric->disabled);
    }
    
    //
    protected function enable()
    {
        $this->salesPerformanceMetric->enable();
    }
    public function test_enable_setDisabledFalse()
    {
        $this->salesPerformanceMetric->disabled = true;
        $this->enable();
        $this->assertFalse($this->salesPerformanceMetric->disabled);
    }
}

class TestableSalesPerformanceMetric extends SalesPerformanceMetric
{
    public string $id;
    public bool $disabled;
    public DateTimeImmutable $createdTime;
    public DateTimeImmutable $lastModifiedTime;
    public string $name;
    public SalesPerformanceMetricType $metricType;
    public RecurrenceType $recurrenceType;
    public ?int $recurrenceCount;
    public ?string $displaySchema;
    public Collection $evaluations;
}
