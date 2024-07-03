<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\CommonSalesMetric;
use DateTime;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\MetricType;
use Shared\Domain\Enum\RecurrenceType;
use Tests\Company\Application\Controllers\CompanyControllerTestCase;
use Tests\resources\Application\EntityRecord;

class CommonSalesMetricControllerTest extends CompanyControllerTestCase
{
    protected EntityRecord $commonSalesMetricOne;
    protected EntityRecord $commonSalesMetricTwo;
    
    protected $commonSalesMetricPayload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('CommonSalesMetric')->truncate();
        //
        $this->commonSalesMetricOne = new EntityRecord(CommonSalesMetric::class, 1);
        $this->commonSalesMetricOne->columns['lastModifiedTime'] = (new DateTime('-1 months'))->format('Y-m-d H:i:s');
        $this->commonSalesMetricTwo = new EntityRecord(CommonSalesMetric::class, 2);
        //
        $this->commonSalesMetricPayload = [
            'displaySchema' => 'new display schema',
            'evaluationType' => EvaluationType::SUM->value,
            'metricType' => MetricType::SALES_ACTIVITY_REPORT->value,
            'name' => 'new company metric name',
            'recurrenceCount' => 6,
            'recurrenceType' => RecurrenceType::MONTHLY->value,
            'target' => 3456,
        ];
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('CommonSalesMetric')->truncate();
    }
    
    //
    protected function createCommonSalesMetric()
    {
        $this->prepareAdminDependency();
        
        $this->graphqlQuery = <<<'_QUERY'
mutation (
    $displaySchema: String, $evaluationType: String, $metricType: String, $name: String!, 
    $recurrenceCount: Int, $recurrenceType: String, $target: Int
) {
    createCommonSalesMetric (
        displaySchema: $displaySchema, evaluationType: $evaluationType, metricType: $metricType, name: $name, 
        recurrenceCount: $recurrenceCount, recurrenceType: $recurrenceType, target: $target
    ) {
        id, displaySchema, evaluationType, metricType, name, recurrenceCount, recurrenceType, target
    }
}
_QUERY;
        $this->graphqlVariables = $this->commonSalesMetricPayload;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_createCommonSalesMetric_200()
    {
$this->disableExceptionHandling();
        $this->createCommonSalesMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'displaySchema' => $this->commonSalesMetricPayload['displaySchema'],
            'evaluationType' => $this->commonSalesMetricPayload['evaluationType'],
            'metricType' => $this->commonSalesMetricPayload['metricType'],
            'name' => $this->commonSalesMetricPayload['name'],
            'recurrenceCount' => $this->commonSalesMetricPayload['recurrenceCount'],
            'recurrenceType' => $this->commonSalesMetricPayload['recurrenceType'],
            'target' => $this->commonSalesMetricPayload['target'],
        ]);
        
        $this->seeInDatabase('CommonSalesMetric', [
            'createdTime' => $this->stringOfCurrentTime(),
            'lastModifiedTime' => $this->stringOfCurrentTime(),
            'displaySchema' => $this->commonSalesMetricPayload['displaySchema'],
            'evaluationType' => $this->commonSalesMetricPayload['evaluationType'],
            'metricType' => $this->commonSalesMetricPayload['metricType'],
            'name' => $this->commonSalesMetricPayload['name'],
            'recurrenceCount' => $this->commonSalesMetricPayload['recurrenceCount'],
            'recurrenceType' => $this->commonSalesMetricPayload['recurrenceType'],
            'target' => $this->commonSalesMetricPayload['target'],
        ]);
    }
    
    //
    protected function updateCommonSalesMetric()
    {
        $this->prepareAdminDependency();
        $this->commonSalesMetricOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation (
    $id: ID,
    $displaySchema: String, $evaluationType: String, $metricType: String, $name: String!, 
    $recurrenceCount: Int, $recurrenceType: String, $target: Int
) {
    updateCommonSalesMetric (
        id: $id,
        displaySchema: $displaySchema, evaluationType: $evaluationType, metricType: $metricType, name: $name, 
        recurrenceCount: $recurrenceCount, recurrenceType: $recurrenceType, target: $target
    ) {
        id, displaySchema, evaluationType, metricType, name, recurrenceCount, recurrenceType, target
    }
}
_QUERY;
        $this->graphqlVariables = [
            ...$this->commonSalesMetricPayload,
            'id' => $this->commonSalesMetricOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_updateCommonSalesMetric_200()
    {
$this->disableExceptionHandling();
        $this->updateCommonSalesMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'id' => $this->commonSalesMetricOne->columns['id'],
            'displaySchema' => $this->commonSalesMetricPayload['displaySchema'],
            'evaluationType' => $this->commonSalesMetricPayload['evaluationType'],
            'metricType' => $this->commonSalesMetricPayload['metricType'],
            'name' => $this->commonSalesMetricPayload['name'],
            'recurrenceCount' => $this->commonSalesMetricPayload['recurrenceCount'],
            'recurrenceType' => $this->commonSalesMetricPayload['recurrenceType'],
            'target' => $this->commonSalesMetricPayload['target'],
        ]);
        
        $this->seeInDatabase('CommonSalesMetric', [
            'id' => $this->commonSalesMetricOne->columns['id'],
            'lastModifiedTime' => $this->stringOfCurrentTime(),
            'displaySchema' => $this->commonSalesMetricPayload['displaySchema'],
            'evaluationType' => $this->commonSalesMetricPayload['evaluationType'],
            'metricType' => $this->commonSalesMetricPayload['metricType'],
            'name' => $this->commonSalesMetricPayload['name'],
            'recurrenceCount' => $this->commonSalesMetricPayload['recurrenceCount'],
            'recurrenceType' => $this->commonSalesMetricPayload['recurrenceType'],
            'target' => $this->commonSalesMetricPayload['target'],
        ]);
    }
    
    //
    protected function disableCommonSalesMetric()
    {
        $this->prepareAdminDependency();
        $this->commonSalesMetricOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID ) {
    disableCommonSalesMetric ( id: $id ) {
        disabled
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->commonSalesMetricOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_disableCommonSalesMetric_200()
    {
$this->disableExceptionHandling();
        $this->disableCommonSalesMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'disabled' => true,
        ]);
        
        $this->seeInDatabase('CommonSalesMetric', [
            'id' => $this->commonSalesMetricOne->columns['id'],
            'disabled' => true,
        ]);
    }
    
    //
    protected function enableCommonSalesMetric()
    {
        $this->prepareAdminDependency();
        
        $this->commonSalesMetricOne->columns['disabled'] = true;
        $this->commonSalesMetricOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID ) {
    enableCommonSalesMetric ( id: $id ) {
        disabled
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->commonSalesMetricOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_enableCommonSalesMetric_200()
    {
$this->disableExceptionHandling();
        $this->enableCommonSalesMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'disabled' => false,
        ]);
        
        $this->seeInDatabase('CommonSalesMetric', [
            'id' => $this->commonSalesMetricOne->columns['id'],
            'disabled' => false,
        ]);
    }
    
    //
    protected function viewCommonSalesMetricDetail()
    {
        $this->prepareAdminDependency();
        $this->commonSalesMetricOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $id: ID ) {
    viewCommonSalesMetricDetail ( id: $id ) {
        id, displaySchema, evaluationType, metricType, name, recurrenceCount, recurrenceType, target
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->commonSalesMetricOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewCommonSalesMetricDetail_200()
    {
$this->disableExceptionHandling();
        $this->viewCommonSalesMetricDetail();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'id' => $this->commonSalesMetricOne->columns['id'],
            'displaySchema' => $this->commonSalesMetricOne->columns['displaySchema'],
            'evaluationType' => $this->commonSalesMetricOne->columns['evaluationType'],
            'metricType' => $this->commonSalesMetricOne->columns['metricType'],
            'name' => $this->commonSalesMetricOne->columns['name'],
            'recurrenceCount' => $this->commonSalesMetricOne->columns['recurrenceCount'],
            'recurrenceType' => $this->commonSalesMetricOne->columns['recurrenceType'],
            'target' => $this->commonSalesMetricOne->columns['target'],
        ]);
    }
    
    //
    protected function viewCommonSalesMetricList()
    {
        $this->prepareAdminDependency();
        $this->commonSalesMetricOne->insert($this->connection);
        $this->commonSalesMetricTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query {
    viewCommonSalesMetricList {
        list {
            id, evaluationType, metricType, name, recurrenceCount, recurrenceType, target
        },
        cursorLimit { total }
    }
}
_QUERY;
        $this->graphqlVariables = $this->getPaginationInput(true);
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewCommonSalesMetricList_200()
    {
$this->disableExceptionHandling();
        $this->viewCommonSalesMetricList();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->commonSalesMetricOne->columns['id'],
                    'evaluationType' => $this->commonSalesMetricOne->columns['evaluationType'],
                    'metricType' => $this->commonSalesMetricOne->columns['metricType'],
                    'name' => $this->commonSalesMetricOne->columns['name'],
                    'recurrenceCount' => $this->commonSalesMetricOne->columns['recurrenceCount'],
                    'recurrenceType' => $this->commonSalesMetricOne->columns['recurrenceType'],
                    'target' => $this->commonSalesMetricOne->columns['target'],
                ],
                [
                    'id' => $this->commonSalesMetricTwo->columns['id'],
                    'evaluationType' => $this->commonSalesMetricTwo->columns['evaluationType'],
                    'metricType' => $this->commonSalesMetricTwo->columns['metricType'],
                    'name' => $this->commonSalesMetricTwo->columns['name'],
                    'recurrenceCount' => $this->commonSalesMetricTwo->columns['recurrenceCount'],
                    'recurrenceType' => $this->commonSalesMetricTwo->columns['recurrenceType'],
                    'target' => $this->commonSalesMetricTwo->columns['target'],
                ],
            ],
            'cursorLimit' => ['total' => 2],
        ]);
    }
}
