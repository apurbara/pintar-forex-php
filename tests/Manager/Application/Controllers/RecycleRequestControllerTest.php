<?php

namespace Manager\Application\Controllers;

use Company\Domain\Model\Customer;
use Manager\Domain\Model\Manager\Sales;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment\RecycleRequest;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use SharedContext\Domain\Enum\ManagementApprovalStatus;
use SharedContext\Domain\Enum\SalesType;
use Tests\Http\Record\EntityRecord;
use Tests\Manager\Application\Controllers\ManagerControllerTestCase;

class RecycleRequestControllerTest extends ManagerControllerTestCase
{
    protected $salesOne;
    protected $salesTwo;
    
    protected $customerOne;
    protected $customerTwo;

    protected $customerAssignmentOne;
    protected $customerAssignmentTwo;

    protected $recycleRequestOne;
    protected $recycleRequestTwo;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('RecycleRequest')->truncate();
        
        $this->salesOne = new EntityRecord(Sales::class, 1);
        $this->salesOne->columns['Manager_id'] = $this->manager->columns['id'];
        $this->salesOne->columns['type'] = SalesType::IN_HOUSE->value;
        $this->salesTwo = new EntityRecord(Sales::class, 2);
        $this->salesTwo->columns['type'] = SalesType::FREELANCE->value;
        $this->salesTwo->columns['Manager_id'] = $this->manager->columns['id'];
        
        $this->customerOne = new EntityRecord(Customer::class, 1);
        $this->customerTwo = new EntityRecord(Customer::class, 2);
        
        $this->customerAssignmentOne = new EntityRecord(CustomerAssignment::class, 1);
        $this->customerAssignmentOne->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->customerAssignmentOne->columns['Sales_id'] = $this->salesOne->columns['id'];
        $this->customerAssignmentTwo = new EntityRecord(CustomerAssignment::class, 2);
        $this->customerAssignmentTwo->columns['Customer_id'] = $this->customerTwo->columns['id'];
        $this->customerAssignmentTwo->columns['Sales_id'] = $this->salesTwo->columns['id'];
        
        $this->recycleRequestOne = new EntityRecord(RecycleRequest::class, 1);
        $this->recycleRequestOne->columns['CustomerAssignment_id'] = $this->customerAssignmentOne->columns['id'];
        $this->recycleRequestTwo = new EntityRecord(RecycleRequest::class, 2);
        $this->recycleRequestTwo->columns['CustomerAssignment_id'] = $this->customerAssignmentTwo->columns['id'];
        
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('RecycleRequest')->truncate();
    }
    
    //
    protected function accept()
    {
        $this->persistManagerDependency();
        
        $this->salesOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->recycleRequestOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID!, $remark: String ) {
    approveRecycleRequest ( id: $id, remark: $remark ) {
        id, status, note, remark, concludedTime
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->recycleRequestOne->columns['id'],
            'remark' => 'new manager remark',
        ];
        $this->postGraphqlRequest($this->manager->token);
    }
    public function test_accept_200()
    {
        $this->accept();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->recycleRequestOne->columns['id'],
            'status' => ManagementApprovalStatus::APPROVED->value,
            'note' => $this->recycleRequestOne->columns['note'],
            'remark' => $this->graphqlVariables['remark'],
            'concludedTime' => $this->stringOfJakartaCurrentTime(),
        ]);
        
        $this->seeInDatabase('RecycleRequest', [
            'id' => $this->recycleRequestOne->columns['id'],
            'status' => ManagementApprovalStatus::APPROVED->value,
            'remark' => $this->graphqlVariables['remark'],
            'concludedTime' => $this->stringOfCurrentTime(),
        ]);
        $this->seeInDatabase('CustomerAssignment', [
            'id' => $this->customerAssignmentOne->columns['id'],
            'status' => CustomerAssignmentStatus::RECYCLED->value,
        ]);
    }
//    public function test_accept_distributeCustomerToFreelancer()
//    {
//        $this->markTestSkipped();
//        $this->salesTwo->insert($this->connection);
//        $this->accept();
//        
//        $this->seeInDatabase('CustomerAssignment', [
//            'Sales_id' => $this->salesTwo->columns['id'],
//            'Customer_id' => $this->customerOne->columns['id'],
//            'status' => CustomerAssignmentStatus::ACTIVE,
//        ]);
//    }
//    public function test_accept_setInitialScheduleForNewAssignment()
//    {
//        $this->markTestSkipped();
//        $this->salesTwo->insert($this->connection);
//        $this->accept();
//        
//        $startTime = match ((new DateTimeImmutable())->format('w')){
//            '4', '5' => (new DateTimeImmutable('next monday'))->setTime(10, 0)->format('Y-m-d H:i:s'),
//            default => (new DateTimeImmutable('+1 days'))->setTime(10, 0)->format('Y-m-d H:i:s'),
//        };
//        $this->seeInDatabase('SalesActivitySchedule', [
//            'startTime' => $startTime,
//        ]);
//    }
    
    //
    protected function reject()
    {
        $this->persistManagerDependency();
        $this->salesOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->recycleRequestOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID!, $remark: String ) {
    rejectRecycleRequest ( id: $id, remark: $remark ) {
        id, status, note, remark, concludedTime
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->recycleRequestOne->columns['id'],
            'remark' => 'new manager remark',
        ];
        $this->postGraphqlRequest($this->manager->token);
    }
    public function test_reject_200()
    {
        $this->reject();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->recycleRequestOne->columns['id'],
            'status' => ManagementApprovalStatus::REJECTED->value,
            'note' => $this->recycleRequestOne->columns['note'],
            'remark' => $this->graphqlVariables['remark'],
            'concludedTime' => $this->stringOfJakartaCurrentTime(),
        ]);
        
        $this->seeInDatabase('RecycleRequest', [
            'id' => $this->recycleRequestOne->columns['id'],
            'status' => ManagementApprovalStatus::REJECTED->value,
            'remark' => $this->graphqlVariables['remark'],
            'concludedTime' => $this->stringOfCurrentTime(),
        ]);
        $this->seeInDatabase('CustomerAssignment', [
            'id' => $this->customerAssignmentOne->columns['id'],
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
        $this->recycleRequestOne->insert($this->connection);
        
        $this->salesTwo->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerAssignmentTwo->insert($this->connection);
        $this->recycleRequestTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $filters: [FilterInput]) {
    recycleRequestList ( filters: $filters) {
        list { 
            id, status, createdTime, note, 
            customerAssignment { customer { name }, sales { name } } 
        },
        cursorLimit { total, cursorToNextPage }
    }
}
_QUERY;
        $this->graphqlVariables['filters'] = [
            ['column' => 'RecycleRequest.status', 'value' => ManagementApprovalStatus::WAITING_FOR_APPROVAL->value],
        ];
        $this->postGraphqlRequest($this->manager->token);
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
                            'name' => $this->customerOne->columns['name'],
                        ],
                        'sales' => [
                            'name' => $this->salesOne->columns['name'],
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
        $this->recycleRequestOne->columns['status'] = ManagementApprovalStatus::REJECTED->value;
        $this->viewList();
        $this->seeJsonDoesntContains(['id' => $this->recycleRequestOne->columns['id']]);
        $this->seeJsonContains(['id' => $this->recycleRequestTwo->columns['id']]);
        $this->seeJsonContains(['total' => 1]);
    }
    
    //
    protected function viewDetail()
    {
        $this->persistManagerDependency();
        $this->salesOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->recycleRequestOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $id: ID!) {
    recycleRequestDetail ( id: $id ) {
        id, status, createdTime, note, 
        customerAssignment { customer { name }, sales { name } } 
    }
}
_QUERY;
        $this->graphqlVariables['id'] = $this->recycleRequestOne->columns['id'];
        $this->postGraphqlRequest($this->manager->token);
    }
    public function test_viewDetail_200()
    {
        $this->viewDetail();
        $this->seeJsonContains([
            'id' => $this->recycleRequestOne->columns['id'],
            'status' => $this->recycleRequestOne->columns['status'],
            'createdTime' => $this->jakartaDateTimeFormat($this->recycleRequestOne->columns['createdTime']),
            'note' => $this->recycleRequestOne->columns['note'],
            'customerAssignment' => [
                'customer' => [
                    'name' => $this->customerOne->columns['name'],
                ],
                'sales' => [
                    'name' => $this->salesOne->columns['name'],
                ],
            ],
        ]);
    }
    
    protected function viewRecycleRequestCount()
    {
        $this->persistManagerDependency();
        $this->salesOne->insert($this->connection);
        $this->salesTwo->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->customerAssignmentTwo->insert($this->connection);
        $this->recycleRequestOne->insert($this->connection);
        $this->recycleRequestTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $filters: [FilterInput] ) {
    viewRecycleRequestCount ( filters: $filters )
}
_QUERY;
        $this->postGraphqlRequest($this->manager->token);
    }
    public function test_viewRecycleRequestCount_pending_200()
    {
        $this->recycleRequestOne->columns['status'] = ManagementApprovalStatus::WAITING_FOR_APPROVAL->value;
        $this->recycleRequestTwo->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->graphqlVariables =  [
            'filters' => [
                ['column' => 'RecycleRequest.status', 'value' => ManagementApprovalStatus::WAITING_FOR_APPROVAL->value],
            ],
        ];
        $this->viewRecycleRequestCount();
        $this->seeStatusCode(200);
        $this->seeJsonContains(['viewRecycleRequestCount' => 1]);
    }
    public function test_viewRecycleRequestCount_completed_200()
    {
        $this->recycleRequestOne->columns['status'] = ManagementApprovalStatus::REJECTED->value;
        $this->recycleRequestTwo->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->graphqlVariables =  [
            'filters' => [
                ['column' => 'RecycleRequest.status', 'value' => ManagementApprovalStatus::WAITING_FOR_APPROVAL->value, 'comparisonType' => 'NEQ'],
            ],
        ];
        $this->viewRecycleRequestCount();
        $this->seeStatusCode(200);
        $this->seeJsonContains(['viewRecycleRequestCount' => 2]);
    }
}
