<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\Customer;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\Manager\Sales;
use Company\Domain\Model\Manager\Sales\CustomerAssignment;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\RecycleRequest;
use Company\Domain\Model\Province\City;
use Company\Domain\Model\SalesActivity;
use DateTimeImmutable;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Shared\Domain\Enum\SalesType;
use Tests\Company\Application\Controllers\CompanyControllerTestCase;
use Tests\resources\Application\EntityRecord;

class RecycleRequestControllerTest extends CompanyControllerTestCase
{
    protected $city;
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
        $this->connection->table('City')->truncate();
        $this->connection->table('CustomerJourney')->truncate();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerJourney')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('RecycleRequest')->truncate();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
        
        $this->city = new EntityRecord(City::class, 'main');
        
        $this->initialCustomerJourney = new EntityRecord(CustomerJourney::class, 'main');
        $this->initialCustomerJourney->columns['initial'] = true;
        
        $this->initialSalesActivity = new EntityRecord(SalesActivity::class, 'main');
        $this->initialSalesActivity->columns['initial'] = true;
        
        $this->salesOne = new EntityRecord(Sales::class, 1);
        $this->salesOne->columns['type'] = SalesType::IN_HOUSE->value;
        $this->salesOne->columns['City_id'] = $this->city->columns['id'];
        $this->salesTwo = new EntityRecord(Sales::class, 2);
        $this->salesTwo->columns['type'] = SalesType::FREELANCE->value;
        $this->salesTwo->columns['City_id'] = $this->city->columns['id'];
        
        $this->customerOne = new EntityRecord(Customer::class, 1);
        $this->customerOne->columns['City_id'] = $this->city->columns['id'];
        $this->customerTwo = new EntityRecord(Customer::class, 2);
        $this->customerTwo->columns['City_id'] = $this->city->columns['id'];
        
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
        $this->connection->table('City')->truncate();
        $this->connection->table('CustomerJourney')->truncate();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerJourney')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('RecycleRequest')->truncate();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
    }
    
    //
    protected function viewList()
    {
        $this->prepareAdminDependency();
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
        $this->postGraphqlRequest($this->admin->token);
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
        $this->prepareAdminDependency();
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
        $this->postGraphqlRequest($this->admin->token);
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
        $this->prepareAdminDependency();
        $this->recycleRequestOne->insert($this->connection);
        $this->recycleRequestTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $filters: [FilterInput] ) {
    viewRecycleRequestCount ( filters: $filters )
}
_QUERY;
        $this->postGraphqlRequest($this->admin->token);
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
