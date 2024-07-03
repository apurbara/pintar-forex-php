<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\SalesPerformanceMetric;
use Company\Domain\Model\SalesPerformanceMetric\SalesPerformanceMetricEvaluation;
use DateTime;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\RecurrenceType;
use Shared\Domain\Enum\SalesPerformanceMetricType;
use Tests\Company\Application\Controllers\CompanyControllerTestCase;
use Tests\resources\Application\EntityRecord;

class SalesPerformanceMetricControllerTest extends CompanyControllerTestCase
{
    protected EntityRecord $salesPerformanceMetricOne;
    protected EntityRecord $salesPerformanceMetricTwo;
    
    protected EntityRecord $salesPerformanceMetricEvaluationOneA;
    protected EntityRecord $salesPerformanceMetricEvaluationOneB;
    
    protected $salesPerformanceMetricPayload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('SalesPerformanceMetric')->truncate();
        $this->connection->table('SalesPerformanceMetricEvaluation')->truncate();
        //
        $this->salesPerformanceMetricOne = new EntityRecord(SalesPerformanceMetric::class, 1);
        $this->salesPerformanceMetricOne->columns['lastModifiedTime'] = (new DateTime('-1 months'))->format('Y-m-d H:i:s');
        $this->salesPerformanceMetricTwo = new EntityRecord(SalesPerformanceMetric::class, 2);
        
        $this->salesPerformanceMetricEvaluationOneA = new EntityRecord(SalesPerformanceMetricEvaluation::class, 'OneA');
        $this->salesPerformanceMetricEvaluationOneA->columns['SalesPerformanceMetric_id'] = $this->salesPerformanceMetricOne->columns['id'];
        $this->salesPerformanceMetricEvaluationOneA->columns['evaluationType'] = EvaluationType::AVG->value;
        $this->salesPerformanceMetricEvaluationOneB = new EntityRecord(SalesPerformanceMetricEvaluation::class, 'OneB');
        $this->salesPerformanceMetricEvaluationOneB->columns['SalesPerformanceMetric_id'] = $this->salesPerformanceMetricOne->columns['id'];
        $this->salesPerformanceMetricEvaluationOneB->columns['evaluationType'] = EvaluationType::MIN->value;
        //
        $this->salesPerformanceMetricPayload = [
            'displaySchema' => 'new display schema',
            'metricType' => SalesPerformanceMetricType::SALES_ACTIVITY_REPORT->value,
            'name' => 'new company metric name',
            'recurrenceCount' => 6,
            'recurrenceType' => RecurrenceType::MONTHLY->value,
            'evaluations' => [
                [
                    'alias' => 'max activity',
                    'evaluationType' => EvaluationType::MAX->value,
                ],
                [
                    'alias' => 'min activity',
                    'evaluationType' => EvaluationType::MIN->value,
                ],
            ],
        ];
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('SalesPerformanceMetric')->truncate();
        $this->connection->table('SalesPerformanceMetricEvaluation')->truncate();
    }
    
    //
    protected function createSalesPerformanceMetric()
    {
        $this->prepareAdminDependency();
        
        $this->graphqlQuery = <<<'_QUERY'
mutation (
    $displaySchema: String, $metricType: String, $name: String!, $recurrenceCount: Int, $recurrenceType: String,
    $evaluations: [SalesPerformanceMetricEvaluationInput]
) {
    createSalesPerformanceMetric (
        displaySchema: $displaySchema, metricType: $metricType, name: $name, recurrenceCount: $recurrenceCount, 
        recurrenceType: $recurrenceType,
        evaluations: $evaluations
    ) {
        id, displaySchema, metricType, name, recurrenceCount, recurrenceType,
        evaluations { alias, evaluationType }
    }
}
_QUERY;
        $this->graphqlVariables = $this->salesPerformanceMetricPayload;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_createSalesPerformanceMetric_200()
    {
$this->disableExceptionHandling();
        $this->createSalesPerformanceMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'displaySchema' => $this->salesPerformanceMetricPayload['displaySchema'],
            'metricType' => $this->salesPerformanceMetricPayload['metricType'],
            'name' => $this->salesPerformanceMetricPayload['name'],
            'recurrenceCount' => $this->salesPerformanceMetricPayload['recurrenceCount'],
            'recurrenceType' => $this->salesPerformanceMetricPayload['recurrenceType'],
        ]);
        
        $this->seeInDatabase('SalesPerformanceMetric', [
            'createdTime' => $this->stringOfCurrentTime(),
            'lastModifiedTime' => $this->stringOfCurrentTime(),
            'displaySchema' => $this->salesPerformanceMetricPayload['displaySchema'],
            'metricType' => $this->salesPerformanceMetricPayload['metricType'],
            'name' => $this->salesPerformanceMetricPayload['name'],
            'recurrenceCount' => $this->salesPerformanceMetricPayload['recurrenceCount'],
            'recurrenceType' => $this->salesPerformanceMetricPayload['recurrenceType'],
        ]);
    }
    public function test_createSalesPerformanceMetric_appendEvaluations()
    {
        $this->createSalesPerformanceMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'alias' => $this->salesPerformanceMetricPayload['evaluations'][0]['alias'],
            'evaluationType' => $this->salesPerformanceMetricPayload['evaluations'][0]['evaluationType'],
        ]);
        $this->seeJsonContains([
            'alias' => $this->salesPerformanceMetricPayload['evaluations'][1]['alias'],
            'evaluationType' => $this->salesPerformanceMetricPayload['evaluations'][1]['evaluationType'],
        ]);
        
        $this->seeInDatabase('SalesPerformanceMetricEvaluation', [
            'alias' => $this->salesPerformanceMetricPayload['evaluations'][0]['alias'],
            'evaluationType' => $this->salesPerformanceMetricPayload['evaluations'][0]['evaluationType'],
        ]);
        $this->seeInDatabase('SalesPerformanceMetricEvaluation', [
            'alias' => $this->salesPerformanceMetricPayload['evaluations'][1]['alias'],
            'evaluationType' => $this->salesPerformanceMetricPayload['evaluations'][1]['evaluationType'],
        ]);
    }
    
    //
    protected function updateSalesPerformanceMetric()
    {
        $this->prepareAdminDependency();
        $this->salesPerformanceMetricOne->insert($this->connection);
        $this->salesPerformanceMetricEvaluationOneA->insert($this->connection);
        $this->salesPerformanceMetricEvaluationOneB->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation (
    $id: ID,
    $displaySchema: String, $metricType: String, $name: String!, $recurrenceCount: Int, $recurrenceType: String,
    $evaluations: [SalesPerformanceMetricEvaluationInput]
) {
    updateSalesPerformanceMetric (
        id: $id,
        displaySchema: $displaySchema, metricType: $metricType, name: $name, 
        recurrenceCount: $recurrenceCount, recurrenceType: $recurrenceType,
        evaluations: $evaluations
    ) {
        id, displaySchema, metricType, name, recurrenceCount, recurrenceType,
        evaluations { alias, evaluationType }
    }
}
_QUERY;
        $this->graphqlVariables = [
            ...$this->salesPerformanceMetricPayload,
            'id' => $this->salesPerformanceMetricOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_updateSalesPerformanceMetric_200()
    {
$this->disableExceptionHandling();
        $this->updateSalesPerformanceMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'id' => $this->salesPerformanceMetricOne->columns['id'],
            'displaySchema' => $this->salesPerformanceMetricPayload['displaySchema'],
            'metricType' => $this->salesPerformanceMetricPayload['metricType'],
            'name' => $this->salesPerformanceMetricPayload['name'],
            'recurrenceCount' => $this->salesPerformanceMetricPayload['recurrenceCount'],
            'recurrenceType' => $this->salesPerformanceMetricPayload['recurrenceType'],
        ]);
        
        $this->seeInDatabase('SalesPerformanceMetric', [
            'id' => $this->salesPerformanceMetricOne->columns['id'],
            'lastModifiedTime' => $this->stringOfCurrentTime(),
            'displaySchema' => $this->salesPerformanceMetricPayload['displaySchema'],
            'metricType' => $this->salesPerformanceMetricPayload['metricType'],
            'name' => $this->salesPerformanceMetricPayload['name'],
            'recurrenceCount' => $this->salesPerformanceMetricPayload['recurrenceCount'],
            'recurrenceType' => $this->salesPerformanceMetricPayload['recurrenceType'],
        ]);
    }
    public function test_updateSalesPerformanceMetric_addNewEvaluation()
    {
$this->disableExceptionHandling();
        $this->updateSalesPerformanceMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'alias' => $this->salesPerformanceMetricPayload['evaluations'][0]['alias'],
            'evaluationType' => $this->salesPerformanceMetricPayload['evaluations'][0]['evaluationType'],
        ]);
        
        $this->seeInDatabase('SalesPerformanceMetricEvaluation', [
            'alias' => $this->salesPerformanceMetricPayload['evaluations'][0]['alias'],
            'evaluationType' => $this->salesPerformanceMetricPayload['evaluations'][0]['evaluationType'],
            'removed' => false,
        ]);
        
    }
    public function test_updateSalesPerformanceMetric_updateRelevantEvaluation()
    {
$this->disableExceptionHandling();
        $this->updateSalesPerformanceMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'alias' => $this->salesPerformanceMetricPayload['evaluations'][1]['alias'],
            'evaluationType' => $this->salesPerformanceMetricEvaluationOneB->columns['evaluationType'],
        ]);
        
        $this->seeInDatabase('SalesPerformanceMetricEvaluation', [
            'id' => $this->salesPerformanceMetricEvaluationOneB->columns['id'],
            'alias' => $this->salesPerformanceMetricPayload['evaluations'][1]['alias'],
            'evaluationType' => $this->salesPerformanceMetricEvaluationOneB->columns['evaluationType'],
            'removed' => false,
        ]);
        
    }
    public function test_updateSalesPerformanceMetric_removeIrrelevantEvaluation()
    {
$this->disableExceptionHandling();
        $this->updateSalesPerformanceMetric();
        $this->seeStatusCode(200);
        
        $this->seeInDatabase('SalesPerformanceMetricEvaluation', [
            'id' => $this->salesPerformanceMetricEvaluationOneA->columns['id'],
            'removed' => true,
        ]);
        
    }
    
    //
    protected function disableSalesPerformanceMetric()
    {
        $this->prepareAdminDependency();
        $this->salesPerformanceMetricOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID ) {
    disableSalesPerformanceMetric ( id: $id ) {
        disabled
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->salesPerformanceMetricOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_disableSalesPerformanceMetric_200()
    {
$this->disableExceptionHandling();
        $this->disableSalesPerformanceMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'disabled' => true,
        ]);
        
        $this->seeInDatabase('SalesPerformanceMetric', [
            'id' => $this->salesPerformanceMetricOne->columns['id'],
            'disabled' => true,
        ]);
    }
    
    //
    protected function enableSalesPerformanceMetric()
    {
        $this->prepareAdminDependency();
        
        $this->salesPerformanceMetricOne->columns['disabled'] = true;
        $this->salesPerformanceMetricOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID ) {
    enableSalesPerformanceMetric ( id: $id ) {
        disabled
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->salesPerformanceMetricOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_enableSalesPerformanceMetric_200()
    {
$this->disableExceptionHandling();
        $this->enableSalesPerformanceMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'disabled' => false,
        ]);
        
        $this->seeInDatabase('SalesPerformanceMetric', [
            'id' => $this->salesPerformanceMetricOne->columns['id'],
            'disabled' => false,
        ]);
    }
    
    //
    protected function viewSalesPerformanceMetricDetail()
    {
        $this->prepareAdminDependency();
        $this->salesPerformanceMetricOne->insert($this->connection);
        $this->salesPerformanceMetricEvaluationOneA->insert($this->connection);
        $this->salesPerformanceMetricEvaluationOneB->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $id: ID ) {
    viewSalesPerformanceMetricDetail ( id: $id ) {
        id, displaySchema, metricType, name, recurrenceCount, recurrenceType,
        evaluations { alias, evaluationType }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->salesPerformanceMetricOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewSalesPerformanceMetricDetail_200()
    {
$this->disableExceptionHandling();
        $this->viewSalesPerformanceMetricDetail();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'id' => $this->salesPerformanceMetricOne->columns['id'],
            'displaySchema' => $this->salesPerformanceMetricOne->columns['displaySchema'],
            'metricType' => $this->salesPerformanceMetricOne->columns['metricType'],
            'name' => $this->salesPerformanceMetricOne->columns['name'],
            'recurrenceCount' => $this->salesPerformanceMetricOne->columns['recurrenceCount'],
            'recurrenceType' => $this->salesPerformanceMetricOne->columns['recurrenceType'],
            'evaluations' => [
                [
                    'alias' => $this->salesPerformanceMetricEvaluationOneA->columns['alias'],
                    'evaluationType' => $this->salesPerformanceMetricEvaluationOneA->columns['evaluationType'],
                ],
                [
                    'alias' => $this->salesPerformanceMetricEvaluationOneB->columns['alias'],
                    'evaluationType' => $this->salesPerformanceMetricEvaluationOneB->columns['evaluationType'],
                ],
            ],
        ]);
    }
    
    //
    protected function viewSalesPerformanceMetricList()
    {
        $this->prepareAdminDependency();
        $this->salesPerformanceMetricOne->insert($this->connection);
        $this->salesPerformanceMetricTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query {
    viewSalesPerformanceMetricList {
        list {
            id, metricType, name, recurrenceCount, recurrenceType
        },
        cursorLimit { total }
    }
}
_QUERY;
        $this->graphqlVariables = $this->getPaginationInput(true);
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewSalesPerformanceMetricList_200()
    {
$this->disableExceptionHandling();
        $this->viewSalesPerformanceMetricList();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->salesPerformanceMetricOne->columns['id'],
                    'metricType' => $this->salesPerformanceMetricOne->columns['metricType'],
                    'name' => $this->salesPerformanceMetricOne->columns['name'],
                    'recurrenceCount' => $this->salesPerformanceMetricOne->columns['recurrenceCount'],
                    'recurrenceType' => $this->salesPerformanceMetricOne->columns['recurrenceType'],
                ],
                [
                    'id' => $this->salesPerformanceMetricTwo->columns['id'],
                    'metricType' => $this->salesPerformanceMetricTwo->columns['metricType'],
                    'name' => $this->salesPerformanceMetricTwo->columns['name'],
                    'recurrenceCount' => $this->salesPerformanceMetricTwo->columns['recurrenceCount'],
                    'recurrenceType' => $this->salesPerformanceMetricTwo->columns['recurrenceType'],
                ],
            ],
            'cursorLimit' => ['total' => 2],
        ]);
    }
}
