<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\SalesRank;
use DateTime;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\MetricType;
use Shared\Domain\Enum\QueryOrder;
use Shared\Domain\Enum\RecurrenceType;
use Tests\Company\Application\Controllers\CompanyControllerTestCase;
use Tests\resources\Application\EntityRecord;

class SalesRankControllerTest extends CompanyControllerTestCase
{
    protected EntityRecord $salesRankOne;
    protected EntityRecord $salesRankTwo;
    
    protected $salesRankPayload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('SalesRank')->truncate();
        //
        $this->salesRankOne = new EntityRecord(SalesRank::class, 1);
        $this->salesRankOne->columns['lastModifiedTime'] = (new DateTime('-1 months'))->format('Y-m-d H:i:s');
        $this->salesRankTwo = new EntityRecord(SalesRank::class, 2);
        //
        $this->salesRankPayload = [
            'name' => 'new company metric name',
            'metricType' => MetricType::SALES_ACTIVITY_REPORT->value,
            'evaluationType' => EvaluationType::SUM->value,
            'recurrenceType' => RecurrenceType::MONTHLY->value,
            'displaySalesNumber' => 6,
            'queryOrder' => QueryOrder::DESC->value,
            'displaySchema' => 'new display schema',
        ];
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('SalesRank')->truncate();
    }
    
    //
    protected function createSalesRank()
    {
        $this->prepareAdminDependency();
        
        $this->graphqlQuery = <<<'_QUERY'
mutation (
    $displaySchema: String, $evaluationType: String, $metricType: String, $name: String!, 
    $displaySalesNumber: Int, $recurrenceType: String, $queryOrder: String
) {
    createSalesRank (
        displaySchema: $displaySchema, evaluationType: $evaluationType, metricType: $metricType, name: $name, 
        displaySalesNumber: $displaySalesNumber, recurrenceType: $recurrenceType, queryOrder: $queryOrder
    ) {
        id, displaySchema, evaluationType, metricType, name, displaySalesNumber, recurrenceType, queryOrder
    }
}
_QUERY;
        $this->graphqlVariables = $this->salesRankPayload;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_createSalesRank_200()
    {
$this->disableExceptionHandling();
        $this->createSalesRank();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'displaySchema' => $this->salesRankPayload['displaySchema'],
            'evaluationType' => $this->salesRankPayload['evaluationType'],
            'metricType' => $this->salesRankPayload['metricType'],
            'name' => $this->salesRankPayload['name'],
            'displaySalesNumber' => $this->salesRankPayload['displaySalesNumber'],
            'recurrenceType' => $this->salesRankPayload['recurrenceType'],
            'queryOrder' => $this->salesRankPayload['queryOrder'],
        ]);
        
        $this->seeInDatabase('SalesRank', [
            'createdTime' => $this->stringOfCurrentTime(),
            'lastModifiedTime' => $this->stringOfCurrentTime(),
            'displaySchema' => $this->salesRankPayload['displaySchema'],
            'evaluationType' => $this->salesRankPayload['evaluationType'],
            'metricType' => $this->salesRankPayload['metricType'],
            'name' => $this->salesRankPayload['name'],
            'displaySalesNumber' => $this->salesRankPayload['displaySalesNumber'],
            'recurrenceType' => $this->salesRankPayload['recurrenceType'],
            'queryOrder' => $this->salesRankPayload['queryOrder'],
        ]);
    }
    
    //
    protected function updateSalesRank()
    {
        $this->prepareAdminDependency();
        $this->salesRankOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation (
    $id: ID,
    $displaySchema: String, $evaluationType: String, $metricType: String, $name: String!, 
    $displaySalesNumber: Int, $recurrenceType: String, $queryOrder: String
) {
    updateSalesRank (
        id: $id,
        displaySchema: $displaySchema, evaluationType: $evaluationType, metricType: $metricType, name: $name, 
        displaySalesNumber: $displaySalesNumber, recurrenceType: $recurrenceType, queryOrder: $queryOrder
    ) {
        id, displaySchema, evaluationType, metricType, name, displaySalesNumber, recurrenceType, queryOrder
    }
}
_QUERY;
        $this->graphqlVariables = [
            ...$this->salesRankPayload,
            'id' => $this->salesRankOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_updateSalesRank_200()
    {
$this->disableExceptionHandling();
        $this->updateSalesRank();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'id' => $this->salesRankOne->columns['id'],
            'displaySchema' => $this->salesRankPayload['displaySchema'],
            'evaluationType' => $this->salesRankPayload['evaluationType'],
            'metricType' => $this->salesRankPayload['metricType'],
            'name' => $this->salesRankPayload['name'],
            'displaySalesNumber' => $this->salesRankPayload['displaySalesNumber'],
            'recurrenceType' => $this->salesRankPayload['recurrenceType'],
            'queryOrder' => $this->salesRankPayload['queryOrder'],
        ]);
        
        $this->seeInDatabase('SalesRank', [
            'id' => $this->salesRankOne->columns['id'],
            'lastModifiedTime' => $this->stringOfCurrentTime(),
            'displaySchema' => $this->salesRankPayload['displaySchema'],
            'evaluationType' => $this->salesRankPayload['evaluationType'],
            'metricType' => $this->salesRankPayload['metricType'],
            'name' => $this->salesRankPayload['name'],
            'displaySalesNumber' => $this->salesRankPayload['displaySalesNumber'],
            'recurrenceType' => $this->salesRankPayload['recurrenceType'],
            'queryOrder' => $this->salesRankPayload['queryOrder'],
        ]);
    }
    
    //
    protected function disableSalesRank()
    {
        $this->prepareAdminDependency();
        $this->salesRankOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID ) {
    disableSalesRank ( id: $id ) {
        disabled
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->salesRankOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_disableSalesRank_200()
    {
$this->disableExceptionHandling();
        $this->disableSalesRank();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'disabled' => true,
        ]);
        
        $this->seeInDatabase('SalesRank', [
            'id' => $this->salesRankOne->columns['id'],
            'disabled' => true,
        ]);
    }
    
    //
    protected function enableSalesRank()
    {
        $this->prepareAdminDependency();
        
        $this->salesRankOne->columns['disabled'] = true;
        $this->salesRankOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID ) {
    enableSalesRank ( id: $id ) {
        disabled
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->salesRankOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_enableSalesRank_200()
    {
$this->disableExceptionHandling();
        $this->enableSalesRank();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'disabled' => false,
        ]);
        
        $this->seeInDatabase('SalesRank', [
            'id' => $this->salesRankOne->columns['id'],
            'disabled' => false,
        ]);
    }
    
    //
    protected function viewSalesRankDetail()
    {
        $this->prepareAdminDependency();
        $this->salesRankOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $id: ID ) {
    viewSalesRankDetail ( id: $id ) {
        id, displaySchema, evaluationType, metricType, name, displaySalesNumber, recurrenceType, queryOrder
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->salesRankOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewSalesRankDetail_200()
    {
$this->disableExceptionHandling();
        $this->viewSalesRankDetail();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'id' => $this->salesRankOne->columns['id'],
            'displaySchema' => $this->salesRankOne->columns['displaySchema'],
            'evaluationType' => $this->salesRankOne->columns['evaluationType'],
            'metricType' => $this->salesRankOne->columns['metricType'],
            'name' => $this->salesRankOne->columns['name'],
            'displaySalesNumber' => $this->salesRankOne->columns['displaySalesNumber'],
            'recurrenceType' => $this->salesRankOne->columns['recurrenceType'],
            'queryOrder' => $this->salesRankOne->columns['queryOrder'],
        ]);
    }
    
    //
    protected function viewSalesRankList()
    {
        $this->prepareAdminDependency();
        $this->salesRankOne->insert($this->connection);
        $this->salesRankTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query {
    viewSalesRankList {
        list {
            id, evaluationType, metricType, name, displaySalesNumber, recurrenceType, queryOrder
        },
        cursorLimit { total }
    }
}
_QUERY;
        $this->graphqlVariables = $this->getPaginationInput(true);
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewSalesRankList_200()
    {
$this->disableExceptionHandling();
        $this->viewSalesRankList();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->salesRankOne->columns['id'],
                    'evaluationType' => $this->salesRankOne->columns['evaluationType'],
                    'metricType' => $this->salesRankOne->columns['metricType'],
                    'name' => $this->salesRankOne->columns['name'],
                    'displaySalesNumber' => $this->salesRankOne->columns['displaySalesNumber'],
                    'recurrenceType' => $this->salesRankOne->columns['recurrenceType'],
                    'queryOrder' => $this->salesRankOne->columns['queryOrder'],
                ],
                [
                    'id' => $this->salesRankTwo->columns['id'],
                    'evaluationType' => $this->salesRankTwo->columns['evaluationType'],
                    'metricType' => $this->salesRankTwo->columns['metricType'],
                    'name' => $this->salesRankTwo->columns['name'],
                    'displaySalesNumber' => $this->salesRankTwo->columns['displaySalesNumber'],
                    'recurrenceType' => $this->salesRankTwo->columns['recurrenceType'],
                    'queryOrder' => $this->salesRankTwo->columns['queryOrder'],
                ],
            ],
            'cursorLimit' => ['total' => 2],
        ]);
    }
}
