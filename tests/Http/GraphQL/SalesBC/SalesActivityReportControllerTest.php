<?php

namespace Tests\Http\GraphQL\SalesBC;

use Company\Domain\Model\SalesActivity;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule\SalesActivityReport;
use Tests\Http\Record\EntityRecord;

class SalesActivityReportControllerTest extends SalesBCTestCase
{
    protected $salesActivity;
    
    protected $customer;
    
    protected $assignedCustomer;
    
    protected $salesActivitySchedule;
    
    protected $salesActivityReportOne;
    protected $salesActivityReportTwo;
    
    protected $submitReportRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('AssignedCustomer')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
        $this->connection->table('SalesActivityReport')->truncate();
        
        $this->salesActivity = new EntityRecord(SalesActivity::class, 'main');
        
        $this->customer = new EntityRecord(Customer::class, 'main');
        
        $this->assignedCustomer = new EntityRecord(AssignedCustomer::class, 'main');
        $this->assignedCustomer->columns['Customer_id'] = $this->customer->columns['id'];
        $this->assignedCustomer->columns['Sales_id'] = $this->sales->columns['id'];
        
        $this->salesActivitySchedule = new EntityRecord(SalesActivitySchedule::class, 'main');
        $this->salesActivitySchedule->columns['AssignedCustomer_id'] = $this->assignedCustomer->columns['id'];
        $this->salesActivitySchedule->columns['SalesActivity_id'] = $this->salesActivity->columns['id'];
        
        $this->salesActivityReportOne = new EntityRecord(SalesActivityReport::class, 1);
        $this->salesActivityReportOne->columns['SalesActivitySchedule_id'] = $this->salesActivitySchedule->columns['id'];
        $this->salesActivityReportTwo = new EntityRecord(SalesActivityReport::class, 2);
        $this->salesActivityReportTwo->columns['SalesActivitySchedule_id'] = $this->salesActivitySchedule->columns['id'];
        
        $this->submitReportRequest = [
            'content' => 'next report content',
        ];
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('AssignedCustomer')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
        $this->connection->table('SalesActivityReport')->truncate();
    }
    
    //
    protected function submitSchedule()
    {
        $this->prepareSalesDependency();
        $this->salesActivity->insert($this->connection);
        $this->assignedCustomer->insert($this->connection);
        $this->salesActivitySchedule->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $salesId: ID!, $salesActivityScheduleId: ID!, $content: String ) {
    sales ( salesId: $salesId ) {
        salesActivitySchedule ( salesActivityScheduleId: $salesActivityScheduleId ) {
            submitReport ( content: $content ) {
                id, content, submitTime
            }
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'salesId' => $this->sales->columns['id'],
            'salesActivityScheduleId' => $this->salesActivitySchedule->columns['id'],
            ...$this->submitReportRequest
        ];
        $this->postGraphqlRequest($this->personnel->token);
    }
    public function test_submitSchedule_200()
    {
        $this->submitSchedule();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'content' => $this->submitReportRequest['content'],
            'submitTime' => $this->stringOfJakartaCurrentTime(),
        ]);
        
        $this->seeInDatabase('SalesActivityReport', [
            'SalesActivitySchedule_id' => $this->salesActivitySchedule->columns['id'],
            'content' => $this->submitReportRequest['content'],
            'submitTime' => $this->stringOfJakartaCurrentTime(),
        ]);
    }
    
    //
    protected function viewList()
    {
        $this->prepareSalesDependency();
        
        $this->salesActivity->insert($this->connection);
        $this->customer->insert($this->connection);
        $this->assignedCustomer->insert($this->connection);
        $this->salesActivitySchedule->insert($this->connection);
        
        $this->salesActivityReportOne->insert($this->connection);
        $this->salesActivityReportTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $salesId: ID!) {
    sales ( salesId: $salesId ) {
        salesActivityReportList {
            list { id, submitTime, content },
            cursorLimit { total, cursorToNextPage }
        }
    }
}
_QUERY;
        $this->graphqlVariables['salesId'] = $this->sales->columns['id'];
        $this->postGraphqlRequest($this->personnel->token);
    }
    public function test_viewList_200()
    {
        $this->viewList();
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->salesActivityReportOne->columns['id'],
                    'content' => $this->salesActivityReportOne->columns['content'],
                    'submitTime' => $this->jakartaDateTimeFormat($this->salesActivityReportOne->columns['submitTime']),
                ],
                [
                    'id' => $this->salesActivityReportTwo->columns['id'],
                    'content' => $this->salesActivityReportTwo->columns['content'],
                    'submitTime' => $this->jakartaDateTimeFormat($this->salesActivityReportTwo->columns['submitTime']),
                ],
            ],
            'cursorLimit' => [
                'total' => 2,
                'cursorToNextPage' => null,
            ],
        ]);
    }
    
    //
    protected function viewDetail()
    {
        $this->prepareSalesDependency();
        $this->salesActivity->insert($this->connection);
        $this->customer->insert($this->connection);
        $this->assignedCustomer->insert($this->connection);
        $this->salesActivitySchedule->insert($this->connection);
        
        $this->salesActivityReportOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $salesId: ID!, $id: ID!) {
    sales ( salesId: $salesId ) {
        salesActivityReportDetail ( salesActivityReportId: $id ) {
            id, content, submitTime
        }
    }
}
_QUERY;
        $this->graphqlVariables['salesId'] = $this->sales->columns['id'];
        $this->graphqlVariables['id'] = $this->salesActivityReportOne->columns['id'];
        $this->postGraphqlRequest($this->personnel->token);
    }
    public function test_viewDetail_200()
    {
        $this->viewDetail();
        $this->seeJsonContains([
            'id' => $this->salesActivityReportOne->columns['id'],
            'content' => $this->salesActivityReportOne->columns['content'],
            'submitTime' => $this->jakartaDateTimeFormat($this->salesActivityReportOne->columns['submitTime']),
        ]);
    }
}
