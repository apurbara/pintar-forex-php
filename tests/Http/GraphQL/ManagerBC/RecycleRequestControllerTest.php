<?php

namespace Tests\Http\GraphQL\ManagerBC;

use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\Personnel;
use Company\Domain\Model\Personnel\Manager\Sales;
use Company\Domain\Model\SalesActivity;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\RecycleRequest;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use SharedContext\Domain\Enum\ManagementApprovalStatus;
use SharedContext\Domain\Enum\SalesType;
use Tests\Http\Record\EntityRecord;

class RecycleRequestControllerTest extends ManagerBCTestCase
{
    protected $personnelOne;
    protected $personnelTwo;
    
    protected $area;
    protected $initialCustomerJourney;
    protected $initialSalesActivity;

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
        $this->connection->table('Area')->truncate();
        $this->connection->table('CustomerJourney')->truncate();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerJourney')->truncate();
        $this->connection->table('AssignedCustomer')->truncate();
        $this->connection->table('RecycleRequest')->truncate();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
        
        $this->area = new EntityRecord(Area::class, 'main');
        
        $this->initialCustomerJourney = new EntityRecord(CustomerJourney::class, 'main');
        $this->initialCustomerJourney->columns['initial'] = true;
        
        $this->initialSalesActivity = new EntityRecord(SalesActivity::class, 'main');
        $this->initialSalesActivity->columns['initial'] = true;
        
        $this->personnelOne = new EntityRecord(Personnel::class, 1);
        $this->personnelTwo = new EntityRecord(Personnel::class, 2);
        
        $this->salesOne = new EntityRecord(Sales::class, 1);
        $this->salesOne->columns['Personnel_id'] = $this->personnelOne->columns['id'];
        $this->salesOne->columns['Manager_id'] = $this->manager->columns['id'];
        $this->salesOne->columns['type'] = SalesType::IN_HOUSE->value;
        $this->salesOne->columns['Area_id'] = $this->area->columns['id'];
        $this->salesTwo = new EntityRecord(Sales::class, 2);
        $this->salesTwo->columns['Personnel_id'] = $this->personnelTwo->columns['id'];
        $this->salesTwo->columns['Manager_id'] = $this->manager->columns['id'];
        $this->salesTwo->columns['type'] = SalesType::FREELANCE->value;
        $this->salesTwo->columns['Area_id'] = $this->area->columns['id'];
        
        $this->customerOne = new EntityRecord(Customer::class, 1);
        $this->customerOne->columns['Area_id'] = $this->area->columns['id'];
        $this->customerTwo = new EntityRecord(Customer::class, 2);
        $this->customerTwo->columns['Area_id'] = $this->area->columns['id'];
        
        $this->customerAssignmentOne = new EntityRecord(AssignedCustomer::class, 1);
        $this->customerAssignmentOne->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->customerAssignmentOne->columns['Sales_id'] = $this->salesOne->columns['id'];
        $this->customerAssignmentTwo = new EntityRecord(AssignedCustomer::class, 2);
        $this->customerAssignmentTwo->columns['Customer_id'] = $this->customerTwo->columns['id'];
        $this->customerAssignmentTwo->columns['Sales_id'] = $this->salesTwo->columns['id'];
        
        $this->recycleRequestOne = new EntityRecord(RecycleRequest::class, 1);
        $this->recycleRequestOne->columns['AssignedCustomer_id'] = $this->customerAssignmentOne->columns['id'];
        $this->recycleRequestTwo = new EntityRecord(RecycleRequest::class, 2);
        $this->recycleRequestTwo->columns['AssignedCustomer_id'] = $this->customerAssignmentTwo->columns['id'];
        
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Area')->truncate();
        $this->connection->table('CustomerJourney')->truncate();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerJourney')->truncate();
        $this->connection->table('AssignedCustomer')->truncate();
        $this->connection->table('RecycleRequest')->truncate();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
    }
    
    //
    protected function accept()
    {
        $this->prepareManagerDependency();
        
        $this->area->insert($this->connection);
        $this->initialCustomerJourney->insert($this->connection);
        $this->initialSalesActivity->insert($this->connection);
        $this->personnelOne->insert($this->connection);
        $this->salesOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->recycleRequestOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $managerId: ID!, $id: ID!, $remark: String ) {
    manager ( managerId: $managerId ) {
        approveRecycleRequest ( id: $id, remark: $remark ) {
            id, status, note, remark, concludedTime
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'managerId' => $this->manager->columns['id'],
            'id' => $this->recycleRequestOne->columns['id'],
            'remark' => 'new manager remark',
        ];
        $this->postGraphqlRequest($this->personnel->token);
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
        $this->seeInDatabase('AssignedCustomer', [
            'id' => $this->customerAssignmentOne->columns['id'],
            'status' => CustomerAssignmentStatus::RECYCLED->value,
        ]);
    }
    public function test_accept_distributeCustomerToFreelancer()
    {
$this->disableExceptionHandling();
        $this->salesTwo->insert($this->connection);
        $this->accept();
        
        $this->seeInDatabase('AssignedCustomer', [
            'Sales_id' => $this->salesTwo->columns['id'],
            'Customer_id' => $this->customerOne->columns['id'],
            'status' => CustomerAssignmentStatus::ACTIVE,
        ]);
    }
    public function test_accept_setInitialScheduleForNewAssignment()
    {
        $this->salesTwo->insert($this->connection);
        $this->accept();
        
        $startTime = match ((new \DateTimeImmutable())->format('w')){
            '4', '5' => (new \DateTimeImmutable('next monday'))->setTime(10, 0)->format('Y-m-d H:i:s'),
            default => (new \DateTimeImmutable('+1 days'))->setTime(10, 0)->format('Y-m-d H:i:s'),
        };
        $this->seeInDatabase('SalesActivitySchedule', [
            'startTime' => $startTime,
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
        $this->recycleRequestOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $managerId: ID!, $id: ID!, $remark: String ) {
    manager ( managerId: $managerId ) {
        rejectRecycleRequest ( id: $id, remark: $remark ) {
            id, status, note, remark, concludedTime
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'managerId' => $this->manager->columns['id'],
            'id' => $this->recycleRequestOne->columns['id'],
            'remark' => 'new manager remark',
        ];
        $this->postGraphqlRequest($this->personnel->token);
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
        $this->seeInDatabase('AssignedCustomer', [
            'id' => $this->customerAssignmentOne->columns['id'],
            'status' => CustomerAssignmentStatus::ACTIVE->value,
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
        $this->recycleRequestOne->insert($this->connection);
        
        $this->personnelTwo->insert($this->connection);
        $this->salesTwo->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerAssignmentTwo->insert($this->connection);
        $this->recycleRequestTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $managerId: ID!, $filters: [FilterInput]) {
    manager ( managerId: $managerId ) {
        recycleRequestList ( filters: $filters) {
            list { 
                id, status, createdTime, note, 
                assignedCustomer { customer { name }, sales { personnel { name } } } 
            },
            cursorLimit { total, cursorToNextPage }
        }
    }
}
_QUERY;
        $this->graphqlVariables['managerId'] = $this->manager->columns['id'];
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
                    'id' => $this->recycleRequestTwo->columns['id'],
                    'status' => $this->recycleRequestTwo->columns['status'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->recycleRequestTwo->columns['createdTime']),
                    'note' => $this->recycleRequestTwo->columns['note'],
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
        $this->recycleRequestOne->columns['status'] = ManagementApprovalStatus::REJECTED->value;
        $this->viewList();
        $this->seeJsonDoesntContains(['id' => $this->recycleRequestOne->columns['id']]);
        $this->seeJsonContains(['id' => $this->recycleRequestTwo->columns['id']]);
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
        $this->recycleRequestOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $managerId: ID!, $id: ID!) {
    manager ( managerId: $managerId ) {
        recycleRequestDetail ( id: $id ) {
            id, status, createdTime, note, 
            assignedCustomer { customer { name }, sales { personnel { name } } } 
        }
    }
}
_QUERY;
        $this->graphqlVariables['managerId'] = $this->manager->columns['id'];
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
