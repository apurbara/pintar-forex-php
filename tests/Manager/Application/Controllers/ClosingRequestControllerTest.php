<?php

namespace Manager\Application\Controllers;

use Company\Domain\Model\Customer;
use Company\Domain\Model\Manager\Sales;
use Company\Domain\Model\Manager\Sales\CustomerAssignment;
use Company\Domain\Model\Manager\Sales\StrikingAssignment;
use Company\Domain\Model\Manager\Sales\StrikingAssignment\ClosingRequest;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Tests\Manager\Application\Controllers\ManagerControllerTestCase;
use Tests\resources\Application\EntityRecord;

class ClosingRequestControllerTest extends ManagerControllerTestCase
{
    protected $salesOne;
    protected $salesTwo;
    
    protected $customerOne;
    protected $customerTwo;

    protected $strikingAssignmentOne, $customerAssignmentOne;
    protected $strikingAssignmentTwo, $customerAssignmentTwo;

    protected $closingRequestOne;
    protected $closingRequestTwo;

    protected $closingRequestPayload = ['remark' => 'manager remark'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('StrikingAssignment')->truncate();
        $this->connection->table('ClosingRequest')->truncate();
        
        $this->salesOne = new EntityRecord(Sales::class, 1);
        $this->salesOne->columns['Manager_id'] = $this->manager->columns['id'];
        $this->salesTwo = new EntityRecord(Sales::class, 2);
        $this->salesTwo->columns['Manager_id'] = $this->manager->columns['id'];
        
        $this->customerOne = new EntityRecord(Customer::class, 1);
        $this->customerTwo = new EntityRecord(Customer::class, 2);
        
        $this->customerAssignmentOne = new EntityRecord(CustomerAssignment::class, 'One');
        $this->strikingAssignmentOne = new EntityRecord(StrikingAssignment::class, 'One');
        $this->strikingAssignmentOne->columns['CustomerAssignment_id'] = $this->customerAssignmentOne->columns['id'];
        $this->strikingAssignmentOne->columns['id'] = $this->customerAssignmentOne->columns['id'];
        $this->strikingAssignmentOne->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->strikingAssignmentOne->columns['Sales_id'] = $this->salesOne->columns['id'];
        $this->customerAssignmentTwo = new EntityRecord(CustomerAssignment::class, 'Two');
        $this->strikingAssignmentTwo = new EntityRecord(StrikingAssignment::class, 'Two');
        $this->strikingAssignmentTwo->columns['CustomerAssignment_id'] = $this->customerAssignmentTwo->columns['id'];
        $this->strikingAssignmentTwo->columns['id'] = $this->customerAssignmentTwo->columns['id'];
        $this->strikingAssignmentTwo->columns['Customer_id'] = $this->customerTwo->columns['id'];
        $this->strikingAssignmentTwo->columns['Sales_id'] = $this->salesTwo->columns['id'];
        
        $this->closingRequestOne = new EntityRecord(ClosingRequest::class, 1);
        $this->closingRequestOne->columns['StrikingAssignment_id'] = $this->strikingAssignmentOne->columns['id'];
        $this->closingRequestTwo = new EntityRecord(ClosingRequest::class, 2);
        $this->closingRequestTwo->columns['StrikingAssignment_id'] = $this->strikingAssignmentTwo->columns['id'];
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('StrikingAssignment')->truncate();
        $this->connection->table('ClosingRequest')->truncate();
    }
    
    //
    protected function accept()
    {
        $this->persistManagerDependency();
        $this->salesOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->strikingAssignmentOne->insert($this->connection);
        $this->closingRequestOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID!, $remark: String ) {
    acceptClosingRequest ( id: $id, remark: $remark ) {
        id, status, transactionValue, note, remark
    }
}
_QUERY;
        $this->graphqlVariables = [
            ...$this->closingRequestPayload,
            'id' => $this->closingRequestOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->manager->token);
    }
    public function test_accept_200()
    {
$this->disableExceptionHandling();
        $this->accept();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->closingRequestOne->columns['id'],
            'status' => ManagementApprovalStatus::APPROVED->value,
            'transactionValue' => $this->closingRequestOne->columns['transactionValue'],
            'note' => $this->closingRequestOne->columns['note'],
            'remark' => $this->closingRequestPayload['remark'],
        ]);
        
        $this->seeInDatabase('ClosingRequest', [
            'id' => $this->closingRequestOne->columns['id'],
            'status' => ManagementApprovalStatus::APPROVED->value,
            'remark' => $this->closingRequestPayload['remark'],
        ]);
        $this->seeInDatabase('StrikingAssignment', [
            'id' => $this->strikingAssignmentOne->columns['id'],
            'status' => CustomerAssignmentStatus::COMPLETED->value,
        ]);
    }
    
    //
    protected function reject()
    {
        $this->persistManagerDependency();
        $this->salesOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->strikingAssignmentOne->insert($this->connection);
        $this->closingRequestOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID! ) {
    rejectClosingRequest ( id: $id ) {
        id, status, transactionValue, note
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->closingRequestOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->manager->token);
    }
    public function test_reject_200()
    {
        $this->reject();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->closingRequestOne->columns['id'],
            'status' => ManagementApprovalStatus::REJECTED->value,
            'transactionValue' => $this->closingRequestOne->columns['transactionValue'],
            'note' => $this->closingRequestOne->columns['note'],
        ]);
        
        $this->seeInDatabase('ClosingRequest', [
            'id' => $this->closingRequestOne->columns['id'],
            'status' => ManagementApprovalStatus::REJECTED->value,
        ]);
        
        $this->seeInDatabase('StrikingAssignment', [
            'id' => $this->strikingAssignmentOne->columns['id'],
            'status' => CustomerAssignmentStatus::ACTIVE->value,
        ]);
    }
    
    //
    protected function viewList()
    {
        $this->persistManagerDependency();
        $this->salesOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->strikingAssignmentOne->insert($this->connection);
        $this->closingRequestOne->insert($this->connection);
        
        $this->salesTwo->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerAssignmentTwo->insert($this->connection);
        $this->strikingAssignmentTwo->insert($this->connection);
        $this->closingRequestTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $filters: [FilterInput]) {
    closingRequestList ( filters: $filters) {
        list { 
            id, status, createdTime, transactionValue, note, 
            strikingAssignment { customer { name }, sales { name } } 
        },
        cursorLimit { total, cursorToNextPage }
    }
}
_QUERY;
        $this->graphqlVariables['filters'] = [
            ['column' => 'ClosingRequest.status', 'value' => ManagementApprovalStatus::WAITING_FOR_APPROVAL->value],
        ];
        $this->postGraphqlRequest($this->manager->token);
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
                    'strikingAssignment' => [
                        'customer' => [
                            'name' => $this->customerOne->columns['name'],
                        ],
                        'sales' => [
                                'name' => $this->salesOne->columns['name'],
                        ],
                    ],
                ],
                [
                    'id' => $this->closingRequestTwo->columns['id'],
                    'status' => $this->closingRequestTwo->columns['status'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->closingRequestTwo->columns['createdTime']),
                    'transactionValue' => $this->closingRequestTwo->columns['transactionValue'],
                    'note' => $this->closingRequestTwo->columns['note'],
                    'strikingAssignment' => [
                        'customer' => [
                            'name' => $this->customerTwo->columns['name'],
                        ],
                        'sales' => [
                            'name' => $this->salesTwo->columns['name'],
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
        $this->persistManagerDependency();
        $this->salesOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->strikingAssignmentOne->insert($this->connection);
        $this->closingRequestOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $id: ID!) {
    closingRequestDetail ( id: $id ) {
        id, status, createdTime, transactionValue, note, 
        strikingAssignment { customer { name }, sales { name } } 
    }
}
_QUERY;
        $this->graphqlVariables['id'] = $this->closingRequestOne->columns['id'];
        $this->postGraphqlRequest($this->manager->token);
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
            'strikingAssignment' => [
                'customer' => [
                    'name' => $this->customerOne->columns['name'],
                ],
                'sales' => [
                    'name' => $this->salesOne->columns['name'],
                ],
            ],
        ]);
    }
    
    //
    protected function viewClosingRequestCount()
    {
        $this->persistManagerDependency();
        $this->salesOne->insert($this->connection);
        $this->salesTwo->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->customerAssignmentTwo->insert($this->connection);
        $this->strikingAssignmentOne->insert($this->connection);
        $this->strikingAssignmentTwo->insert($this->connection);
        $this->closingRequestOne->insert($this->connection);
        $this->closingRequestTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $filters: [FilterInput] ) {
    viewClosingRequestCount ( filters: $filters )
}
_QUERY;
        $this->postGraphqlRequest($this->manager->token);
    }
    public function test_viewClosingRequestCount_200()
    {
$this->disableExceptionHandling();
        $this->viewClosingRequestCount();
        $this->seeStatusCode(200);
        $this->seeJsonContains(['viewClosingRequestCount' => 2]);
    }
    public function test_viewClosingRequestCount_pendingFilter_200()
    {
        $this->closingRequestOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestTwo->columns['status'] = ManagementApprovalStatus::WAITING_FOR_APPROVAL->value;
        $this->graphqlVariables = [
            'filters' => [
                ['column' => 'ClosingRequest.status', 'value' => ManagementApprovalStatus::WAITING_FOR_APPROVAL->value],
            ],
        ];
        $this->viewClosingRequestCount();
        $this->seeStatusCode(200);
        $this->seeJsonContains(['viewClosingRequestCount' => 1]);
    }
    public function test_viewClosingRequestCount_completed()
    {
        $this->closingRequestOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestTwo->columns['status'] = ManagementApprovalStatus::REJECTED->value;
        $this->graphqlVariables = [
            'filters' => [
                ['column' => 'ClosingRequest.status', 'value' => ManagementApprovalStatus::WAITING_FOR_APPROVAL->value, 'comparisonType' => 'NEQ'],
            ],
        ];
        $this->viewClosingRequestCount();
        $this->seeStatusCode(200);
        $this->seeJsonContains(['viewClosingRequestCount' => 2]);
    }
}
