<?php

namespace App\Http\Controllers\SalesBC;

use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\ClosingRequest;
use SharedContext\Domain\Enum\ManagementApprovalStatus;
use Tests\Http\GraphQL\SalesBC\SalesBCTestCase;
use Tests\Http\Record\EntityRecord;

class ClosingRequestControllerTest extends SalesBCTestCase
{
    protected $customer;
    
    protected $assignedCustomer;
    
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
        $this->connection->table('AssignedCustomer')->truncate();
        $this->connection->table('ClosingRequest')->truncate();
        
        $this->customer = new EntityRecord(Customer::class, 'main');
        
        $this->assignedCustomer = new EntityRecord(AssignedCustomer::class, 'main');
        $this->assignedCustomer->columns['Customer_id'] = $this->customer->columns['id'];
        $this->assignedCustomer->columns['Sales_id'] = $this->sales->columns['id'];
        
        $this->closingRequestOne = new EntityRecord(ClosingRequest::class, 1);
        $this->closingRequestOne->columns['AssignedCustomer_id'] = $this->assignedCustomer->columns['id'];
        $this->closingRequestTwo = new EntityRecord(ClosingRequest::class, 2);
        $this->closingRequestTwo->columns['AssignedCustomer_id'] = $this->assignedCustomer->columns['id'];
        
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('AssignedCustomer')->truncate();
        $this->connection->table('ClosingRequest')->truncate();
    }
    
    //
    protected function submit()
    {
        $this->prepareSalesDependency();
        $this->customer->insert($this->connection);
        $this->assignedCustomer->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $salesId: ID!, $AssignedCustomer_id: ID!, $transactionValue: Int, $note: String ) {
    sales ( salesId: $salesId ) {
        submitClosingRequest ( AssignedCustomer_id: $AssignedCustomer_id, transactionValue: $transactionValue, note: $note ) {
            id, status, createdTime, transactionValue, note
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'salesId' => $this->sales->columns['id'],
            'AssignedCustomer_id' => $this->assignedCustomer->columns['id'],
            ...$this->closingRequestPayload
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
            'transactionValue' => $this->closingRequestPayload['transactionValue'],
            'note' => $this->closingRequestPayload['note'],
        ]);
        
        $this->seeInDatabase('ClosingRequest', [
            'AssignedCustomer_id' => $this->assignedCustomer->columns['id'],
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
        $this->assignedCustomer->insert($this->connection);
        $this->closingRequestOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $salesId: ID!, $id: ID!, $transactionValue: Int, $note: String ) {
    sales ( salesId: $salesId ) {
        updateClosingRequest ( id: $id,  transactionValue: $transactionValue, note: $note ) {
            id, status, createdTime, transactionValue, note
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'salesId' => $this->sales->columns['id'],
            'id' => $this->closingRequestOne->columns['id'],
            ...$this->closingRequestPayload,
        ];
        $this->postGraphqlRequest($this->personnel->token);
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
        $this->assignedCustomer->insert($this->connection);
        
        $this->closingRequestOne->insert($this->connection);
        $this->closingRequestTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $salesId: ID!, $filters: [FilterInput]) {
    sales ( salesId: $salesId ) {
        closingRequestList ( filters: $filters) {
            list { id, status, createdTime, transactionValue, note, assignedCustomer { customer { name } } },
            cursorLimit { total, cursorToNextPage }
        }
    }
}
_QUERY;
        $this->graphqlVariables['salesId'] = $this->sales->columns['id'];
        $this->graphqlVariables['filters'] = [
            ['column' => 'ClosingRequest.status', 'value' => ManagementApprovalStatus::WAITING_FOR_APPROVAL->value],
        ];
        $this->postGraphqlRequest($this->personnel->token);
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
                    'assignedCustomer' => [
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
        $this->assignedCustomer->insert($this->connection);
        $this->closingRequestOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $salesId: ID!, $id: ID!) {
    sales ( salesId: $salesId ) {
        closingRequestDetail ( id: $id ) {
            id, status, createdTime, transactionValue, note
        }
    }
}
_QUERY;
        $this->graphqlVariables['salesId'] = $this->sales->columns['id'];
        $this->graphqlVariables['id'] = $this->closingRequestOne->columns['id'];
        $this->postGraphqlRequest($this->personnel->token);
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
