<?php

namespace Company\Domain\Model\SalesPerformanceMetric;

use Company\Domain\Model\SalesPerformanceMetric;
use Company\Domain\Model\SalesPerformanceMetricData;
use Shared\Domain\Enum\EvaluationType;
use Tests\TestBase;

class SalesPerformanceMetricEvaluationTest extends TestBase
{
    protected $salesPerformanceMetric;
    protected $evaluation;
    //
    protected $id = 'newId', $alias = 'new alias', $evaluationType;
    //
    protected $salesPerformanceMetricData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->salesPerformanceMetric = $this->buildMockOfClass(SalesPerformanceMetric::class);
        $data = (new SalesPerformanceMetricEvaluationData())
                ->setAlias('alias')
                ->setEvaluationType(EvaluationType::AVG->value);
        $this->evaluation = new TestableSalesPerformanceMetricEvaluation($this->salesPerformanceMetric, 'id', $data);
        //
        $this->evaluationType = EvaluationType::COUNT->value;
        //
        $this->salesPerformanceMetricData = $this->buildMockOfClass(SalesPerformanceMetricData::class);
    }
    
    //
    protected function createData()
    {
        return (new SalesPerformanceMetricEvaluationData())
                ->setAlias($this->alias)
                ->setEvaluationType($this->evaluationType);
    }
    
    //
    protected function construct()
    {
        return new TestableSalesPerformanceMetricEvaluation($this->salesPerformanceMetric, $this->id, $this->createData());
    }
    public function test_construct_setProperties()
    {
        $evaluation = $this->construct();
        $this->assertSame($this->salesPerformanceMetric, $evaluation->salesPerformanceMetric);
        $this->assertSame($this->id, $evaluation->id);
        $this->assertFalse($evaluation->removed);
        $this->assertSame($this->alias, $evaluation->alias);
        $this->assertSame(EvaluationType::from($this->evaluationType), $evaluation->evaluationType);
    }
    public function test_construct_emptyAlias_forbidden()
    {
        $this->alias = '';
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'evaluation alias is mandatory');
    }
    
    //
    protected function update()
    {
        $this->evaluation->update($this->salesPerformanceMetricData);
    }
    public function test_update_evaluationTypeNotExistInSalesPerformanceMetricData_setRemoved()
    {
        $this->update();
        $this->assertTrue($this->evaluation->removed);
    }
    public function test_update_evaluationTypeExistInSalesPerformanceMetricData_updateAlias()
    {
        $this->salesPerformanceMetricData->expects($this->once())
                ->method('pullEvaluationDataAssociationWithType')
                ->with($this->evaluation->evaluationType->value)
                ->willReturn($this->createData());
        $this->update();
        $this->assertSame($this->alias, $this->evaluation->alias);
    }
    public function test_update_evaluationTypeExistInSalesPerformanceMetricData_setRemovedFalse()
    {
        $this->evaluation->removed = true;
        $this->salesPerformanceMetricData->expects($this->once())
                ->method('pullEvaluationDataAssociationWithType')
                ->with($this->evaluation->evaluationType->value)
                ->willReturn($this->createData());
        $this->update();
        $this->assertFalse($this->evaluation->removed);
    }
    public function test_update_evaluationTypeExistInSalesPerformanceMetricData_emptyAlias_badRequest()
    {
        $this->alias = '';
        $this->salesPerformanceMetricData->expects($this->once())
                ->method('pullEvaluationDataAssociationWithType')
                ->with($this->evaluation->evaluationType->value)
                ->willReturn($this->createData());
        $this->assertRegularExceptionThrowed(fn() => $this->update(), 'Bad Request', 'evaluation alias is mandatory');
    }
}

class TestableSalesPerformanceMetricEvaluation extends SalesPerformanceMetricEvaluation
{
    public SalesPerformanceMetric $salesPerformanceMetric;
    public string $id;
    public bool $removed;
    public string $alias;
    public EvaluationType $evaluationType;
}
