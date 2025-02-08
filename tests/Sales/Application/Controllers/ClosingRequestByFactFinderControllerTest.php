<?php

namespace Sales\Application\Controllers;

use Company\Domain\Model\Customer;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Model\Sales\FactFindingAssignment;
use Sales\Domain\Model\Sales\FactFindingAssignment\ClosingRequestByFactFinder;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Tests\resources\Application\EntityRecord;
use Tests\Sales\Application\Controllers\SalesControllerTestCase;

class ClosingRequestByFactFinderControllerTest extends SalesControllerTestCase
{
    protected $customer;
    
    protected $factFindingAssignment, $customerAssignment;
    
    protected $closingRequestByFactFinderOne;
    protected $closingRequestByFactFinderTwo;
    
    protected $closingRequestByFactFinderPayload = [
        'transactionValue' => 40000000  ,
        'note' => 'closing note',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('FactFindingAssignment')->truncate();
        $this->connection->table('ClosingRequestByFactFinder')->truncate();
        
        $this->customer = new EntityRecord(Customer::class, 'main');
        
        $this->customerAssignment = new EntityRecord(CustomerAssignment::class, 'main');
        $this->factFindingAssignment = new EntityRecord(FactFindingAssignment::class, 'main');
        $this->factFindingAssignment->columns['CustomerAssignment_id'] = $this->customerAssignment->columns['id'];
        $this->factFindingAssignment->columns['id'] = $this->customerAssignment->columns['id'];
        $this->factFindingAssignment->columns['Customer_id'] = $this->customer->columns['id'];
        $this->factFindingAssignment->columns['Sales_id'] = $this->sales->columns['id'];
        
        $this->closingRequestByFactFinderOne = new EntityRecord(ClosingRequestByFactFinder::class, 1);
        $this->closingRequestByFactFinderOne->columns['FactFindingAssignment_id'] = $this->factFindingAssignment->columns['id'];
        $this->closingRequestByFactFinderTwo = new EntityRecord(ClosingRequestByFactFinder::class, 2);
        $this->closingRequestByFactFinderTwo->columns['FactFindingAssignment_id'] = $this->factFindingAssignment->columns['id'];
        
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('FactFindingAssignment')->truncate();
        $this->connection->table('ClosingRequestByFactFinder')->truncate();
    }
    
    //
    protected function submit()
    {
        $this->prepareSalesDependency();
        $this->customer->insert($this->connection);
        $this->customerAssignment->insert($this->connection);
        $this->factFindingAssignment->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $FactFindingAssignment_id: ID!, $transactionValue: Int, $note: String ) {
    submitClosingRequestByFactFinder ( FactFindingAssignment_id: $FactFindingAssignment_id, transactionValue: $transactionValue, note: $note ) {
        id, status, createdTime, transactionValue, note
    }
}
_QUERY;
        $this->graphqlVariables = [
            'FactFindingAssignment_id' => $this->factFindingAssignment->columns['id'],
            ...$this->closingRequestByFactFinderPayload
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_submit_200()
    {
$this->disableExceptionHandling();
        $this->submit();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'status' => ManagementApprovalStatus::WAITING_FOR_APPROVAL->value,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'transactionValue' => $this->closingRequestByFactFinderPayload['transactionValue'],
            'note' => $this->closingRequestByFactFinderPayload['note'],
        ]);
        
        $this->seeInDatabase('ClosingRequestByFactFinder', [
            'FactFindingAssignment_id' => $this->factFindingAssignment->columns['id'],
            'status' => ManagementApprovalStatus::WAITING_FOR_APPROVAL->value,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'transactionValue' => $this->closingRequestByFactFinderPayload['transactionValue'],
            'note' => $this->closingRequestByFactFinderPayload['note'],
        ]);
    }
    
    //
    protected function update()
    {
        $this->prepareSalesDependency();
        $this->customer->insert($this->connection);
        $this->customerAssignment->insert($this->connection);
        $this->factFindingAssignment->insert($this->connection);
        $this->closingRequestByFactFinderOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID!, $transactionValue: Int, $note: String ) {
    updateClosingRequestByFactFinder ( id: $id,  transactionValue: $transactionValue, note: $note ) {
        id, status, createdTime, transactionValue, note
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->closingRequestByFactFinderOne->columns['id'],
            ...$this->closingRequestByFactFinderPayload,
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_update_200()
    {
        $this->update();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->closingRequestByFactFinderOne->columns['id'],
            'transactionValue' => $this->closingRequestByFactFinderPayload['transactionValue'],
            'note' => $this->closingRequestByFactFinderPayload['note'],
        ]);
        
        $this->seeInDatabase('ClosingRequestByFactFinder', [
            'id' => $this->closingRequestByFactFinderOne->columns['id'],
            'transactionValue' => $this->closingRequestByFactFinderPayload['transactionValue'],
            'note' => $this->closingRequestByFactFinderPayload['note'],
        ]);
    }
    
    //
    protected function viewList()
    {
        $this->prepareSalesDependency();
        
        $this->customer->insert($this->connection);
        $this->customerAssignment->insert($this->connection);
        $this->factFindingAssignment->insert($this->connection);
        
        $this->closingRequestByFactFinderOne->insert($this->connection);
        $this->closingRequestByFactFinderTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $filters: [FilterInput]) {
    closingRequestByFactFinderList ( filters: $filters) {
        list { id, status, createdTime, transactionValue, note, factFindingAssignment { customer { name } } },
        cursorLimit { total, cursorToNextPage }
    }
}
_QUERY;
        $this->graphqlVariables['filters'] = [
            ['column' => 'ClosingRequestByFactFinder.status', 'value' => ManagementApprovalStatus::WAITING_FOR_APPROVAL->value],
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_viewList_200()
    {
        $this->viewList();
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->closingRequestByFactFinderOne->columns['id'],
                    'status' => $this->closingRequestByFactFinderOne->columns['status'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->closingRequestByFactFinderOne->columns['createdTime']),
                    'transactionValue' => $this->closingRequestByFactFinderOne->columns['transactionValue'],
                    'note' => $this->closingRequestByFactFinderOne->columns['note'],
                    'factFindingAssignment' => [
                        'customer' => [
                            'name' => $this->customer->columns['name'],
                        ],
                    ],
                ],
                [
                    'id' => $this->closingRequestByFactFinderTwo->columns['id'],
                    'status' => $this->closingRequestByFactFinderTwo->columns['status'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->closingRequestByFactFinderTwo->columns['createdTime']),
                    'transactionValue' => $this->closingRequestByFactFinderTwo->columns['transactionValue'],
                    'note' => $this->closingRequestByFactFinderTwo->columns['note'],
                    'factFindingAssignment' => [
                        'customer' => [
                            'name' => $this->customer->columns['name'],
                        ],
                    ],
                ],
            ],
            'cursorLimit' => [
                'total' => 2,
                'cursorToNextPage' => null,
            ],
        ]);
    }
    public function test_viewList_applyFilter()
    {
        $this->closingRequestByFactFinderOne->columns['status'] = ManagementApprovalStatus::REJECTED->value;
        $this->viewList();
        $this->seeJsonDoesntContains(['id' => $this->closingRequestByFactFinderOne->columns['id']]);
        $this->seeJsonContains(['id' => $this->closingRequestByFactFinderTwo->columns['id']]);
        $this->seeJsonContains(['total' => 1]);
    }
    
    //
    protected function viewDetail()
    {
        $this->prepareSalesDependency();
        $this->customer->insert($this->connection);
        $this->customerAssignment->insert($this->connection);
        $this->factFindingAssignment->insert($this->connection);
        $this->closingRequestByFactFinderOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $id: ID!) {
    closingRequestByFactFinderDetail ( id: $id ) {
        id, status, createdTime, transactionValue, note
    }
}
_QUERY;
        $this->graphqlVariables['id'] = $this->closingRequestByFactFinderOne->columns['id'];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_viewDetail_200()
    {
        $this->viewDetail();
        $this->seeJsonContains([
            'id' => $this->closingRequestByFactFinderOne->columns['id'],
            'status' => $this->closingRequestByFactFinderOne->columns['status'],
            'createdTime' => $this->jakartaDateTimeFormat($this->closingRequestByFactFinderOne->columns['createdTime']),
            'transactionValue' => $this->closingRequestByFactFinderOne->columns['transactionValue'],
            'note' => $this->closingRequestByFactFinderOne->columns['note'],
        ]);
    }
}
