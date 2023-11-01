<?php

namespace Tests\Http\GraphQL\SalesBC;

use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\RecycleRequest;
use SharedContext\Domain\Enum\ManagementApprovalStatus;
use Tests\Http\Record\EntityRecord;

class RecycleRequestControllerTest extends SalesBCTestCase
{
    protected $customer;
    
    protected $assignedCustomer;
    
    protected $recycleRequestOne;
    protected $recycleRequestTwo;
    
    protected $recycleRequestPayload = [
        'note' => 'recycle note',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('AssignedCustomer')->truncate();
        $this->connection->table('RecycleRequest')->truncate();
        
        $this->customer = new EntityRecord(Customer::class, 'main');
        
        $this->assignedCustomer = new EntityRecord(AssignedCustomer::class, 'main');
        $this->assignedCustomer->columns['Customer_id'] = $this->customer->columns['id'];
        $this->assignedCustomer->columns['Sales_id'] = $this->sales->columns['id'];
        
        $this->recycleRequestOne = new EntityRecord(RecycleRequest::class, 1);
        $this->recycleRequestOne->columns['AssignedCustomer_id'] = $this->assignedCustomer->columns['id'];
        $this->recycleRequestTwo = new EntityRecord(RecycleRequest::class, 2);
        $this->recycleRequestTwo->columns['AssignedCustomer_id'] = $this->assignedCustomer->columns['id'];
        
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('AssignedCustomer')->truncate();
        $this->connection->table('RecycleRequest')->truncate();
    }
    
    //
    protected function submit()
    {
        $this->prepareSalesDependency();
        $this->customer->insert($this->connection);
        $this->assignedCustomer->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $salesId: ID!, $assignedCustomerId: ID!, $note: String ) {
    sales ( salesId: $salesId ) {
        assignedCustomer ( assignedCustomerId: $assignedCustomerId) {
            submitRecycleRequest ( note: $note ) {
                id, status, createdTime, note
            }
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'salesId' => $this->sales->columns['id'],
            'assignedCustomerId' => $this->assignedCustomer->columns['id'],
            ...$this->recycleRequestPayload
        ];
        $this->postGraphqlRequest($this->personnel->token);
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
            'AssignedCustomer_id' => $this->assignedCustomer->columns['id'],
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
        $this->assignedCustomer->insert($this->connection);
        $this->recycleRequestOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $salesId: ID!, $recycleRequestId: ID!, $note: String ) {
    sales ( salesId: $salesId ) {
        updateRecycleRequest ( recycleRequestId: $recycleRequestId,  note: $note ) {
            id, status, createdTime, note
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'salesId' => $this->sales->columns['id'],
            'recycleRequestId' => $this->recycleRequestOne->columns['id'],
            ...$this->recycleRequestPayload,
        ];
        $this->postGraphqlRequest($this->personnel->token);
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
        $this->assignedCustomer->insert($this->connection);
        
        $this->recycleRequestOne->insert($this->connection);
        $this->recycleRequestTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $salesId: ID!, $filters: [FilterInput]) {
    sales ( salesId: $salesId ) {
        recycleRequestList ( filters: $filters) {
            list { id, status, createdTime, note, assignedCustomer { customer { name } } },
            cursorLimit { total, cursorToNextPage }
        }
    }
}
_QUERY;
        $this->graphqlVariables['salesId'] = $this->sales->columns['id'];
        $this->graphqlVariables['filters'] = [
            ['column' => 'RecycleRequest.status', 'value' => ManagementApprovalStatus::WAITING_FOR_APPROVAL->value],
        ];
        $this->postGraphqlRequest($this->personnel->token);
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
                    'assignedCustomer' => [
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
                    'assignedCustomer' => [
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
        $this->assignedCustomer->insert($this->connection);
        $this->recycleRequestOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $salesId: ID!, $id: ID!) {
    sales ( salesId: $salesId ) {
        recycleRequestDetail ( recycleRequestId: $id ) {
            id, status, createdTime, note
        }
    }
}
_QUERY;
        $this->graphqlVariables['salesId'] = $this->sales->columns['id'];
        $this->graphqlVariables['id'] = $this->recycleRequestOne->columns['id'];
        $this->postGraphqlRequest($this->personnel->token);
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
