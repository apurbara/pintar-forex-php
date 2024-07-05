<?php

namespace Sales\Application\Controllers;

use Company\Domain\Model\Customer;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Model\Sales\CustomerAssignment\RecycleRequest;
use SharedContext\Domain\Enum\ManagementApprovalStatus;
use Tests\Http\Record\EntityRecord;
use Tests\Sales\Application\Controllers\SalesControllerTestCase;

class RecycleRequestControllerTest extends SalesControllerTestCase
{
    protected $customer;
    
    protected $customerAssignment;
    
    protected $recycleRequestOne;
    protected $recycleRequestTwo;
    
    protected $recycleRequestPayload = [
        'note' => 'recycle note',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('RecycleRequest')->truncate();
        
        $this->customer = new EntityRecord(Customer::class, 'main');
        
        $this->customerAssignment = new EntityRecord(CustomerAssignment::class, 'main');
        $this->customerAssignment->columns['Customer_id'] = $this->customer->columns['id'];
        $this->customerAssignment->columns['Sales_id'] = $this->sales->columns['id'];
        
        $this->recycleRequestOne = new EntityRecord(RecycleRequest::class, 1);
        $this->recycleRequestOne->columns['CustomerAssignment_id'] = $this->customerAssignment->columns['id'];
        $this->recycleRequestTwo = new EntityRecord(RecycleRequest::class, 2);
        $this->recycleRequestTwo->columns['CustomerAssignment_id'] = $this->customerAssignment->columns['id'];
        
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('RecycleRequest')->truncate();
    }
    
    //
    protected function submit()
    {
        $this->prepareSalesDependency();
        $this->customer->insert($this->connection);
        $this->customerAssignment->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $CustomerAssignment_id: ID!, $note: String ) {
    submitRecycleRequest ( CustomerAssignment_id: $CustomerAssignment_id, note: $note ) {
        id, status, createdTime, note
    }
}
_QUERY;
        $this->graphqlVariables = [
            'CustomerAssignment_id' => $this->customerAssignment->columns['id'],
            ...$this->recycleRequestPayload
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_submit_200()
    {
        $this->submit();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'status' => ManagementApprovalStatus::WAITING_FOR_APPROVAL->value,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'note' => $this->recycleRequestPayload['note'],
        ]);
        
        $this->seeInDatabase('RecycleRequest', [
            'CustomerAssignment_id' => $this->customerAssignment->columns['id'],
            'status' => ManagementApprovalStatus::WAITING_FOR_APPROVAL->value,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'note' => $this->recycleRequestPayload['note'],
        ]);
    }
    
    //
    protected function update()
    {
        $this->prepareSalesDependency();
        $this->customer->insert($this->connection);
        $this->customerAssignment->insert($this->connection);
        $this->recycleRequestOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID!, $note: String ) {
    updateRecycleRequest ( id: $id,  note: $note ) {
        id, status, createdTime, note
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->recycleRequestOne->columns['id'],
            ...$this->recycleRequestPayload,
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_update_200()
    {
        $this->update();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->recycleRequestOne->columns['id'],
            'note' => $this->recycleRequestPayload['note'],
        ]);
        
        $this->seeInDatabase('RecycleRequest', [
            'id' => $this->recycleRequestOne->columns['id'],
            'note' => $this->recycleRequestPayload['note'],
        ]);
    }
    
    //
    protected function viewList()
    {
        $this->prepareSalesDependency();
        
        $this->customer->insert($this->connection);
        $this->customerAssignment->insert($this->connection);
        
        $this->recycleRequestOne->insert($this->connection);
        $this->recycleRequestTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $filters: [FilterInput]) {
    recycleRequestList ( filters: $filters) {
        list { id, status, createdTime, note, customerAssignment { customer { name } } },
        cursorLimit { total, cursorToNextPage }
    }
}
_QUERY;
        $this->graphqlVariables['filters'] = [
            ['column' => 'RecycleRequest.status', 'value' => ManagementApprovalStatus::WAITING_FOR_APPROVAL->value],
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_viewList_200()
    {
        $this->viewList();
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->recycleRequestOne->columns['id'],
                    'status' => $this->recycleRequestOne->columns['status'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->recycleRequestOne->columns['createdTime']),
                    'note' => $this->recycleRequestOne->columns['note'],
                    'customerAssignment' => [
                        'customer' => [
                            'name' => $this->customer->columns['name'],
                        ],
                    ],
                ],
                [
                    'id' => $this->recycleRequestTwo->columns['id'],
                    'status' => $this->recycleRequestTwo->columns['status'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->recycleRequestTwo->columns['createdTime']),
                    'note' => $this->recycleRequestTwo->columns['note'],
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
        $this->recycleRequestOne->columns['status'] = ManagementApprovalStatus::REJECTED->value;
        $this->viewList();
        $this->seeJsonDoesntContains(['id' => $this->recycleRequestOne->columns['id']]);
        $this->seeJsonContains(['id' => $this->recycleRequestTwo->columns['id']]);
        $this->seeJsonContains(['total' => 1]);
    }
    
    //
    protected function viewDetail()
    {
        $this->prepareSalesDependency();
        $this->customer->insert($this->connection);
        $this->customerAssignment->insert($this->connection);
        $this->recycleRequestOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $id: ID!) {
    recycleRequestDetail ( id: $id ) {
        id, status, createdTime, note
    }
}
_QUERY;
        $this->graphqlVariables['id'] = $this->recycleRequestOne->columns['id'];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_viewDetail_200()
    {
        $this->viewDetail();
        $this->seeJsonContains([
            'id' => $this->recycleRequestOne->columns['id'],
            'status' => $this->recycleRequestOne->columns['status'],
            'createdTime' => $this->jakartaDateTimeFormat($this->recycleRequestOne->columns['createdTime']),
            'note' => $this->recycleRequestOne->columns['note'],
        ]);
    }
}
