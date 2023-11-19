<?php

namespace Tests\Http\GraphQL\ManagerBC;

use Company\Domain\Model\Personnel;
use Company\Domain\Model\Personnel\Manager\Sales;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\ClosingRequest;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use SharedContext\Domain\Enum\ManagementApprovalStatus;
use Tests\Http\Record\EntityRecord;

class ClosingRequestControllerTest extends ManagerBCTestCase
{
    protected $personnelOne;
    protected $personnelTwo;
    
    protected $salesOne;
    protected $salesTwo;
    
    protected $customerOne;
    protected $customerTwo;

    protected $customerAssignmentOne;
    protected $customerAssignmentTwo;

    protected $closingRequestOne;
    protected $closingRequestTwo;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('AssignedCustomer')->truncate();
        $this->connection->table('ClosingRequest')->truncate();
        
        $this->personnelOne = new EntityRecord(Personnel::class, 1);
        $this->personnelTwo = new EntityRecord(Personnel::class, 2);
        
        $this->salesOne = new EntityRecord(Sales::class, 1);
        $this->salesOne->columns['Personnel_id'] = $this->personnelOne->columns['id'];
        $this->salesOne->columns['Manager_id'] = $this->manager->columns['id'];
        $this->salesTwo = new EntityRecord(Sales::class, 2);
        $this->salesTwo->columns['Personnel_id'] = $this->personnelTwo->columns['id'];
        $this->salesTwo->columns['Manager_id'] = $this->manager->columns['id'];
        
        $this->customerOne = new EntityRecord(Customer::class, 1);
        $this->customerTwo = new EntityRecord(Customer::class, 2);
        
        $this->customerAssignmentOne = new EntityRecord(AssignedCustomer::class, 1);
        $this->customerAssignmentOne->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->customerAssignmentOne->columns['Sales_id'] = $this->salesOne->columns['id'];
        $this->customerAssignmentTwo = new EntityRecord(AssignedCustomer::class, 2);
        $this->customerAssignmentTwo->columns['Customer_id'] = $this->customerTwo->columns['id'];
        $this->customerAssignmentTwo->columns['Sales_id'] = $this->salesTwo->columns['id'];
        
        $this->closingRequestOne = new EntityRecord(ClosingRequest::class, 1);
        $this->closingRequestOne->columns['AssignedCustomer_id'] = $this->customerAssignmentOne->columns['id'];
        $this->closingRequestTwo = new EntityRecord(ClosingRequest::class, 2);
        $this->closingRequestTwo->columns['AssignedCustomer_id'] = $this->customerAssignmentTwo->columns['id'];
        
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('AssignedCustomer')->truncate();
        $this->connection->table('ClosingRequest')->truncate();
    }
    
    //
    protected function accept()
    {
        $this->prepareManagerDependency();
        $this->personnelOne->insert($this->connection);
        $this->salesOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->closingRequestOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $managerId: ID!, $id: ID! ) {
    manager ( managerId: $managerId ) {
        acceptClosingRequest ( id: $id ) {
            id, status, transactionValue, note
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'managerId' => $this->manager->columns['id'],
            'id' => $this->closingRequestOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->personnel->token);
    }
    public function test_accept_200()
    {
        $this->accept();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->closingRequestOne->columns['id'],
            'status' => ManagementApprovalStatus::APPROVED->value,
            'transactionValue' => $this->closingRequestOne->columns['transactionValue'],
            'note' => $this->closingRequestOne->columns['note'],
        ]);
        
        $this->seeInDatabase('ClosingRequest', [
            'id' => $this->closingRequestOne->columns['id'],
            'status' => ManagementApprovalStatus::APPROVED->value,
        ]);
    }
    
    //
    protected function reject()
    {
        $this->prepareManagerDependency();
        $this->personnelOne->insert($this->connection);
        $this->salesOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->closingRequestOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $managerId: ID!, $id: ID! ) {
    manager ( managerId: $managerId ) {
        rejectClosingRequest ( id: $id ) {
            id, status, transactionValue, note
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'managerId' => $this->manager->columns['id'],
            'id' => $this->closingRequestOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->personnel->token);
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
    }
    
    //
    protected function viewList()
    {
        $this->prepareManagerDependency();
        $this->personnelOne->insert($this->connection);
        $this->salesOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->closingRequestOne->insert($this->connection);
        
        $this->personnelTwo->insert($this->connection);
        $this->salesTwo->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerAssignmentTwo->insert($this->connection);
        $this->closingRequestTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $managerId: ID!, $filters: [FilterInput]) {
    manager ( managerId: $managerId ) {
        closingRequestList ( filters: $filters) {
            list { 
                id, status, createdTime, transactionValue, note, 
                assignedCustomer { customer { name }, sales { personnel { name } } } 
            },
            cursorLimit { total, cursorToNextPage }
        }
    }
}
_QUERY;
        $this->graphqlVariables['managerId'] = $this->manager->columns['id'];
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
                            'name' => $this->customerOne->columns['name'],
                        ],
                        'sales' => [
                            'personnel' => [
                                'name' => $this->personnelOne->columns['name'],
                            ],
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
                            'name' => $this->customerTwo->columns['name'],
                        ],
                        'sales' => [
                            'personnel' => [
                                'name' => $this->personnelTwo->columns['name'],
                            ],
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
        $this->prepareManagerDependency();
        $this->personnelOne->insert($this->connection);
        $this->salesOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->closingRequestOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $managerId: ID!, $id: ID!) {
    manager ( managerId: $managerId ) {
        closingRequestDetail ( id: $id ) {
            id, status, createdTime, transactionValue, note, 
            assignedCustomer { customer { name }, sales { personnel { name } } } 
        }
    }
}
_QUERY;
        $this->graphqlVariables['managerId'] = $this->manager->columns['id'];
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
            'assignedCustomer' => [
                'customer' => [
                    'name' => $this->customerOne->columns['name'],
                ],
                'sales' => [
                    'personnel' => [
                        'name' => $this->personnelOne->columns['name'],
                    ],
                ],
            ],
        ]);
    }
}
