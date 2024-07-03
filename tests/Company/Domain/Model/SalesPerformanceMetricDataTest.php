<?php

namespace Company\Domain\Model;

use Company\Domain\Model\SalesPerformanceMetric\SalesPerformanceMetricEvaluationData;
use Shared\Domain\Enum\EvaluationType;
use Tests\TestBase;

class SalesPerformanceMetricDataTest extends TestBase
{
    protected $data;
    //
    protected $evaluationDataOne;
    protected $evaluationDataTwo;
    protected $evaluationDataThree;
    //
    protected $evaluationDataKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->evaluationDataOne = (new SalesPerformanceMetricEvaluationData())->setEvaluationType(EvaluationType::AVG->value);
        $this->evaluationDataTwo = (new SalesPerformanceMetricEvaluationData())->setEvaluationType(EvaluationType::COUNT->value);
        $this->evaluationDataThree = (new SalesPerformanceMetricEvaluationData())->setEvaluationType(EvaluationType::MIN->value);
        //
        $this->data = new TestableSalesPerformanceMetricData();
        $this->data->evaluations[$this->evaluationDataOne->evaluationType] = $this->evaluationDataOne;
        $this->data->evaluations[$this->evaluationDataTwo->evaluationType] = $this->evaluationDataTwo;
        //
        $this->evaluationDataKey = $this->evaluationDataTwo->evaluationType;
    }
    
    //
    protected function addEvaluationData()
    {
        $this->data->addEvaluationData($this->evaluationDataThree);
    }
    public function test_addEvaluationData_appendNewEvaluationData()
    {
        $this->addEvaluationData();
        $this->assertSame($this->evaluationDataThree, $this->data->evaluations[$this->evaluationDataThree->evaluationType]);
    }
    
    //
    protected function pullEvaluationDataAssociationWithType()
    {
        return $this->data->pullEvaluationDataAssociationWithType($this->evaluationDataKey);
    }
    public function test_pullEvaluationDataAssociationWithType_returnCorrespondingEvaluationData()
    {
        $this->assertSame($this->evaluationDataTwo, $this->pullEvaluationDataAssociationWithType());
    }
    public function test_pullEvaluationDataAssociationWithType_removeData()
    {
        $this->pullEvaluationDataAssociationWithType();
        $this->assertFalse(isset($this->data->evaluations[$this->evaluationDataKey]));
    }
    public function test_pullEvaluationDataAssociationWithType_noCorrespondingData()
    {
        $this->evaluationDataKey = $this->evaluationDataThree->evaluationType;
        $this->assertNull($this->pullEvaluationDataAssociationWithType());
    }
}

class TestableSalesPerformanceMetricData extends SalesPerformanceMetricData
{
    public array $evaluations = [];
}
