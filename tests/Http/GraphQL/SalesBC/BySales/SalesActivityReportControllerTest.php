<?php

namespace App\Http\Controllers\SalesBC\BySales;

use Company\Domain\Model\AreaStructure\Area\Customer;
use Company\Domain\Model\SalesActivity;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Tests\Http\GraphQL\SalesBC\SalesBCTestCase;
use Tests\Http\Record\EntityRecord;

class SalesActivityReportControllerTest extends SalesBCTestCase
{
    protected $salesActivity;
    
    protected $customer;
    
    protected $customerAssignment;
    
    protected $salesActivitySchedule;
    
    protected $salesActivityReportOne;
    protected $salesActivityReportTwo;
    
    protected $submitReportRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
        $this->connection->table('SalesActivityReport')->truncate();
        
        $this->salesActivity = new EntityRecord(SalesActivity::class, 'main');
        
        $this->customer = new EntityRecord(Customer::class, 'main');
        
        $this->customerAssignment = new EntityRecord(CustomerAssignment::class, 'main');
        $this->customerAssignment->columns['Customer_id'] = $this->customer->columns['id'];
        $this->customerAssignment->columns['Sales_id'] = $this->sales->columns['id'];
        
        $this->salesActivitySchedule = new EntityRecord(SalesActivitySchedule::class, 'main');
        $this->salesActivitySchedule->columns['CustomerAssignment_id'] = $this->customerAssignment->columns['id'];
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
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
        $this->connection->table('SalesActivityReport')->truncate();
    }
    
    protected function submitInitialSalesActivityReport()
    {
        $this->prepareSalesDependency();
        $this->salesActivity->columns['initial'] = true;
        $this->salesActivity->insert($this->connection);
        $this->customerAssignment->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $CustomerAssignment_id: ID, $content: String ) {
    submitInitialSalesActivityReport ( CustomerAssignment_id: $CustomerAssignment_id, content: $content ) {
        id, status, startTime, endTime,
        salesActivity { name }
        salesActivityReport { content }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'CustomerAssignment_id' => $this->customerAssignment->columns['id'],
            'content' => 'new report content',
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_submitInitialSalesActivityReport_200()
    {
$this->disableExceptionHandling();
        $this->submitInitialSalesActivityReport();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'status' => \SharedContext\Domain\Enum\SalesActivityScheduleStatus::COMPLETED->value,
            'startTime' => $this->jakartaDateTimeFormat((new \DateTime())->setTime((new \DateTime())->format('H'), 00)->format('Y-m-d H:i:s')),
            'endTime' => $this->jakartaDateTimeFormat((new \DateTime())->setTime((new \DateTime())->format('H') + 1, 00)->format('Y-m-d H:i:s')),
            'salesActivity' => [
                'name' => $this->salesActivity->columns['name'],
            ],
            'salesActivityReport' => [
                'content' => $this->graphqlVariables['content'],
            ],
        ]);
        
        $this->seeInDatabase('SalesActivitySchedule', [
            'CustomerAssignment_id' => $this->customerAssignment->columns['id'],
            'status' => \SharedContext\Domain\Enum\SalesActivityScheduleStatus::COMPLETED->value,
            'startTime' => (new \DateTime())->setTime((new \DateTime())->format('H'), 00)->format('Y-m-d H:i:s'),
            'endTime' => (new \DateTime())->setTime((new \DateTime())->format('H')+1, 00)->format('Y-m-d H:i:s'),
        ]);
        
        $this->seeInDatabase('SalesActivityReport', [
            'submitTime' => $this->stringOfCurrentTime(),
            'content' => $this->graphqlVariables['content'],
        ]);
    }
    
    //
    protected function submitReport()
    {
        $this->prepareSalesDependency();
        $this->salesActivity->insert($this->connection);
        $this->customerAssignment->insert($this->connection);
        $this->salesActivitySchedule->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $SalesActivitySchedule_id: ID!, $content: String ) {
    submitSalesActivityReport (SalesActivitySchedule_id: $SalesActivitySchedule_id, content: $content ) {
        id, content, submitTime
    }
}
_QUERY;
        $this->graphqlVariables = [
            'SalesActivitySchedule_id' => $this->salesActivitySchedule->columns['id'],
            ...$this->submitReportRequest
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_submitSchedule_200()
    {
        $this->submitReport();
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
        $this->customerAssignment->insert($this->connection);
        $this->salesActivitySchedule->insert($this->connection);
        
        $this->salesActivityReportOne->insert($this->connection);
        $this->salesActivityReportTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query {
    salesActivityReportList {
        list { id, submitTime, content },
        cursorLimit { total, cursorToNextPage }
    }
}
_QUERY;
        $this->graphqlVariables = $this->getPaginationInput();
        $this->postGraphqlRequest($this->sales->token);
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
        $this->customerAssignment->insert($this->connection);
        $this->salesActivitySchedule->insert($this->connection);
        
        $this->salesActivityReportOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $id: ID!) {
    salesActivityReportDetail ( id: $id ) {
        id, content, submitTime
    }
}
_QUERY;
        $this->graphqlVariables['id'] = $this->salesActivityReportOne->columns['id'];
        $this->postGraphqlRequest($this->sales->token);
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
