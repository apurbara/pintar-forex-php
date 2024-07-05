<?php

namespace Sales\Application\Controllers;

use Company\Domain\Model\Customer;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Model\Sales\CustomerAssignment\ClosingRequest;
use SharedContext\Domain\Enum\ManagementApprovalStatus;
use Tests\resources\Application\EntityRecord;
use Tests\Sales\Application\Controllers\SalesControllerTestCase;

class ClosingRequestControllerTest extends SalesControllerTestCase
{
    protected $customer;
    
    protected $customerAssignment;
    
    protected $closingRequestOne;
    protected $closingRequestTwo;
    
    protected $closingRequestPayload = [
        'transactionValue' => 40000000  ,
        'note' => 'closing note',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('ClosingRequest')->truncate();
        
        $this->customer = new EntityRecord(Customer::class, 'main');
        
        $this->customerAssignment = new EntityRecord(CustomerAssignment::class, 'main');
        $this->customerAssignment->columns['Customer_id'] = $this->customer->columns['id'];
        $this->customerAssignment->columns['Sales_id'] = $this->sales->columns['id'];
        
        $this->closingRequestOne = new EntityRecord(ClosingRequest::class, 1);
        $this->closingRequestOne->columns['CustomerAssignment_id'] = $this->customerAssignment->columns['id'];
        $this->closingRequestTwo = new EntityRecord(ClosingRequest::class, 2);
        $this->closingRequestTwo->columns['CustomerAssignment_id'] = $this->customerAssignment->columns['id'];
        
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('ClosingRequest')->truncate();
    }
    
    //
    protected function submit()
    {
        $this->prepareSalesDependency();
        $this->customer->insert($this->connection);
        $this->customerAssignment->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $CustomerAssignment_id: ID!, $transactionValue: Int, $note: String ) {
    submitClosingRequest ( CustomerAssignment_id: $CustomerAssignment_id, transactionValue: $transactionValue, note: $note ) {
        id, status, createdTime, transactionValue, note
    }
}
_QUERY;
        $this->graphqlVariables = [
            'CustomerAssignment_id' => $this->customerAssignment->columns['id'],
            ...$this->closingRequestPayload
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
            'transactionValue' => $this->closingRequestPayload['transactionValue'],
            'note' => $this->closingRequestPayload['note'],
        ]);
        
        $this->seeInDatabase('ClosingRequest', [
            'CustomerAssignment_id' => $this->customerAssignment->columns['id'],
            'status' => ManagementApprovalStatus::WAITING_FOR_APPROVAL->value,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'transactionValue' => $this->closingRequestPayload['transactionValue'],
            'note' => $this->closingRequestPayload['note'],
        ]);
    }
    
    //
    protected function update()
    {
        $this->prepareSalesDependency();
        $this->customer->insert($this->connection);
        $this->customerAssignment->insert($this->connection);
        $this->closingRequestOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID!, $transactionValue: Int, $note: String ) {
    updateClosingRequest ( id: $id,  transactionValue: $transactionValue, note: $note ) {
        id, status, createdTime, transactionValue, note
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->closingRequestOne->columns['id'],
            ...$this->closingRequestPayload,
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_update_200()
    {
        $this->update();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->closingRequestOne->columns['id'],
            'transactionValue' => $this->closingRequestPayload['transactionValue'],
            'note' => $this->closingRequestPayload['note'],
        ]);
        
        $this->seeInDatabase('ClosingRequest', [
            'id' => $this->closingRequestOne->columns['id'],
            'transactionValue' => $this->closingRequestPayload['transactionValue'],
            'note' => $this->closingRequestPayload['note'],
        ]);
    }
    
    //
    protected function viewList()
    {
        $this->prepareSalesDependency();
        
        $this->customer->insert($this->connection);
        $this->customerAssignment->insert($this->connection);
        
        $this->closingRequestOne->insert($this->connection);
        $this->closingRequestTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $filters: [FilterInput]) {
    closingRequestList ( filters: $filters) {
        list { id, status, createdTime, transactionValue, note, customerAssignment { customer { name } } },
        cursorLimit { total, cursorToNextPage }
    }
}
_QUERY;
        $this->graphqlVariables['filters'] = [
            ['column' => 'ClosingRequest.status', 'value' => ManagementApprovalStatus::WAITING_FOR_APPROVAL->value],
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_viewList_200()
    {
        $this->viewList();
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->closingRequestOne->columns['id'],
                    'status' => $this->closingRequestOne->columns['status'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->closingRequestOne->columns['createdTime']),
                    'transactionValue' => $this->closingRequestOne->columns['transactionValue'],
                    'note' => $this->closingRequestOne->columns['note'],
                    'customerAssignment' => [
                        'customer' => [
                            'name' => $this->customer->columns['name'],
                        ],
                    ],
                ],
                [
                    'id' => $this->closingRequestTwo->columns['id'],
                    'status' => $this->closingRequestTwo->columns['status'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->closingRequestTwo->columns['createdTime']),
                    'transactionValue' => $this->closingRequestTwo->columns['transactionValue'],
                    'note' => $this->closingRequestTwo->columns['note'],
                    'customerAssignment' => [
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
        $this->closingRequestOne->columns['status'] = ManagementApprovalStatus::REJECTED->value;
        $this->viewList();
        $this->seeJsonDoesntContains(['id' => $this->closingRequestOne->columns['id']]);
        $this->seeJsonContains(['id' => $this->closingRequestTwo->columns['id']]);
        $this->seeJsonContains(['total' => 1]);
    }
    
    //
    protected function viewDetail()
    {
        $this->prepareSalesDependency();
        $this->customer->insert($this->connection);
        $this->customerAssignment->insert($this->connection);
        $this->closingRequestOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $id: ID!) {
    closingRequestDetail ( id: $id ) {
        id, status, createdTime, transactionValue, note
    }
}
_QUERY;
        $this->graphqlVariables['id'] = $this->closingRequestOne->columns['id'];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_viewDetail_200()
    {
        $this->viewDetail();
        $this->seeJsonContains([
            'id' => $this->closingRequestOne->columns['id'],
            'status' => $this->closingRequestOne->columns['status'],
            'createdTime' => $this->jakartaDateTimeFormat($this->closingRequestOne->columns['createdTime']),
            'transactionValue' => $this->closingRequestOne->columns['transactionValue'],
            'note' => $this->closingRequestOne->columns['note'],
        ]);
    }
}
