<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use Company\Domain\Model\CompanyMetric;
use DateTime;
use SharedContext\Domain\Enum\EvaluationType;
use SharedContext\Domain\Enum\MetricType;
use SharedContext\Domain\Enum\RecurrenceType;
use Tests\Http\GraphQL\CompanyBC\CompanyBCTestCase;
use Tests\Http\Record\EntityRecord;

class CompanyMetricControllerTest extends CompanyBCTestCase
{
    protected EntityRecord $companyMetricOne;
    protected EntityRecord $companyMetricTwo;
    
    protected $companyMetricPayload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('CompanyMetric')->truncate();
        //
        $this->companyMetricOne = new EntityRecord(CompanyMetric::class, 1);
        $this->companyMetricOne->columns['lastModifiedTime'] = (new DateTime('-1 months'))->format('Y-m-d H:i:s');
        $this->companyMetricTwo = new EntityRecord(CompanyMetric::class, 2);
        //
        $this->companyMetricPayload = [
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
        $this->connection->table('CompanyMetric')->truncate();
    }
    
    //
    protected function createCompanyMetric()
    {
        $this->prepareAdminDependency();
        
        $this->graphqlQuery = <<<'_QUERY'
mutation (
    $displaySchema: String, $evaluationType: String, $metricType: String, $name: String!, 
    $recurrenceCount: Int, $recurrenceType: String, $target: Int
) {
    createCompanyMetric (
        displaySchema: $displaySchema, evaluationType: $evaluationType, metricType: $metricType, name: $name, 
        recurrenceCount: $recurrenceCount, recurrenceType: $recurrenceType, target: $target
    ) {
        id, displaySchema, evaluationType, metricType, name, recurrenceCount, recurrenceType, target
    }
}
_QUERY;
        $this->graphqlVariables = $this->companyMetricPayload;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_createCompanyMetric_200()
    {
$this->disableExceptionHandling();
        $this->createCompanyMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'displaySchema' => $this->companyMetricPayload['displaySchema'],
            'evaluationType' => $this->companyMetricPayload['evaluationType'],
            'metricType' => $this->companyMetricPayload['metricType'],
            'name' => $this->companyMetricPayload['name'],
            'recurrenceCount' => $this->companyMetricPayload['recurrenceCount'],
            'recurrenceType' => $this->companyMetricPayload['recurrenceType'],
            'target' => $this->companyMetricPayload['target'],
        ]);
        
        $this->seeInDatabase('CompanyMetric', [
            'createdTime' => $this->stringOfCurrentTime(),
            'lastModifiedTime' => $this->stringOfCurrentTime(),
            'displaySchema' => $this->companyMetricPayload['displaySchema'],
            'evaluationType' => $this->companyMetricPayload['evaluationType'],
            'metricType' => $this->companyMetricPayload['metricType'],
            'name' => $this->companyMetricPayload['name'],
            'recurrenceCount' => $this->companyMetricPayload['recurrenceCount'],
            'recurrenceType' => $this->companyMetricPayload['recurrenceType'],
            'target' => $this->companyMetricPayload['target'],
        ]);
    }
    
    //
    protected function updateCompanyMetric()
    {
        $this->prepareAdminDependency();
        $this->companyMetricOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation (
    $id: ID,
    $displaySchema: String, $evaluationType: String, $metricType: String, $name: String!, 
    $recurrenceCount: Int, $recurrenceType: String, $target: Int
) {
    updateCompanyMetric (
        id: $id,
        displaySchema: $displaySchema, evaluationType: $evaluationType, metricType: $metricType, name: $name, 
        recurrenceCount: $recurrenceCount, recurrenceType: $recurrenceType, target: $target
    ) {
        id, displaySchema, evaluationType, metricType, name, recurrenceCount, recurrenceType, target
    }
}
_QUERY;
        $this->graphqlVariables = [
            ...$this->companyMetricPayload,
            'id' => $this->companyMetricOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_updateCompanyMetric_200()
    {
$this->disableExceptionHandling();
        $this->updateCompanyMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'id' => $this->companyMetricOne->columns['id'],
            'displaySchema' => $this->companyMetricPayload['displaySchema'],
            'evaluationType' => $this->companyMetricPayload['evaluationType'],
            'metricType' => $this->companyMetricPayload['metricType'],
            'name' => $this->companyMetricPayload['name'],
            'recurrenceCount' => $this->companyMetricPayload['recurrenceCount'],
            'recurrenceType' => $this->companyMetricPayload['recurrenceType'],
            'target' => $this->companyMetricPayload['target'],
        ]);
        
        $this->seeInDatabase('CompanyMetric', [
            'id' => $this->companyMetricOne->columns['id'],
            'lastModifiedTime' => $this->stringOfCurrentTime(),
            'displaySchema' => $this->companyMetricPayload['displaySchema'],
            'evaluationType' => $this->companyMetricPayload['evaluationType'],
            'metricType' => $this->companyMetricPayload['metricType'],
            'name' => $this->companyMetricPayload['name'],
            'recurrenceCount' => $this->companyMetricPayload['recurrenceCount'],
            'recurrenceType' => $this->companyMetricPayload['recurrenceType'],
            'target' => $this->companyMetricPayload['target'],
        ]);
    }
    
    //
    protected function disableCompanyMetric()
    {
        $this->prepareAdminDependency();
        $this->companyMetricOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID ) {
    disableCompanyMetric ( id: $id ) {
        disabled
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->companyMetricOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_disableCompanyMetric_200()
    {
$this->disableExceptionHandling();
        $this->disableCompanyMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'disabled' => true,
        ]);
        
        $this->seeInDatabase('CompanyMetric', [
            'id' => $this->companyMetricOne->columns['id'],
            'disabled' => true,
        ]);
    }
    
    //
    protected function enableCompanyMetric()
    {
        $this->prepareAdminDependency();
        
        $this->companyMetricOne->columns['disabled'] = true;
        $this->companyMetricOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID ) {
    enableCompanyMetric ( id: $id ) {
        disabled
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->companyMetricOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_enableCompanyMetric_200()
    {
$this->disableExceptionHandling();
        $this->enableCompanyMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'disabled' => false,
        ]);
        
        $this->seeInDatabase('CompanyMetric', [
            'id' => $this->companyMetricOne->columns['id'],
            'disabled' => false,
        ]);
    }
    
    //
    protected function viewCompanyMetricDetail()
    {
        $this->prepareAdminDependency();
        $this->companyMetricOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $id: ID ) {
    viewCompanyMetricDetail ( id: $id ) {
        id, displaySchema, evaluationType, metricType, name, recurrenceCount, recurrenceType, target
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->companyMetricOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewCompanyMetricDetail_200()
    {
$this->disableExceptionHandling();
        $this->viewCompanyMetricDetail();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'id' => $this->companyMetricOne->columns['id'],
            'displaySchema' => $this->companyMetricOne->columns['displaySchema'],
            'evaluationType' => $this->companyMetricOne->columns['evaluationType'],
            'metricType' => $this->companyMetricOne->columns['metricType'],
            'name' => $this->companyMetricOne->columns['name'],
            'recurrenceCount' => $this->companyMetricOne->columns['recurrenceCount'],
            'recurrenceType' => $this->companyMetricOne->columns['recurrenceType'],
            'target' => $this->companyMetricOne->columns['target'],
        ]);
    }
    
    //
    protected function viewCompanyMetricList()
    {
        $this->prepareAdminDependency();
        $this->companyMetricOne->insert($this->connection);
        $this->companyMetricTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query {
    viewCompanyMetricList {
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
    public function test_viewCompanyMetricList_200()
    {
$this->disableExceptionHandling();
        $this->viewCompanyMetricList();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->companyMetricOne->columns['id'],
                    'evaluationType' => $this->companyMetricOne->columns['evaluationType'],
                    'metricType' => $this->companyMetricOne->columns['metricType'],
                    'name' => $this->companyMetricOne->columns['name'],
                    'recurrenceCount' => $this->companyMetricOne->columns['recurrenceCount'],
                    'recurrenceType' => $this->companyMetricOne->columns['recurrenceType'],
                    'target' => $this->companyMetricOne->columns['target'],
                ],
                [
                    'id' => $this->companyMetricTwo->columns['id'],
                    'evaluationType' => $this->companyMetricTwo->columns['evaluationType'],
                    'metricType' => $this->companyMetricTwo->columns['metricType'],
                    'name' => $this->companyMetricTwo->columns['name'],
                    'recurrenceCount' => $this->companyMetricTwo->columns['recurrenceCount'],
                    'recurrenceType' => $this->companyMetricTwo->columns['recurrenceType'],
                    'target' => $this->companyMetricTwo->columns['target'],
                ],
            ],
            'cursorLimit' => ['total' => 2],
        ]);
    }
}
