<?php

namespace Sales\Application\Controllers;

use Company\Domain\Model\Customer;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\CustomerVerification;
use Company\Domain\Model\SalesActivity;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\StrikingAssignment;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\CustomerStatus;
use Shared\Domain\Enum\SalesActivityScheduleStatus;
use Shared\Domain\Enum\SalesRole;
use Tests\Http\Record\EntityRecord;
use Tests\Sales\Application\Controllers\SalesControllerTestCase;

class StrikingAssignmentControllerTest extends SalesControllerTestCase
{

    protected $customerOne;
    protected $customerTwo;
    protected EntityRecord $customerJourneyOne, $customerJourneyTwo;
    protected $strikingAssignmentOne, $customerAssignmentOne;
    protected $strikingAssignmentTwo, $customerAssignmentTwo;
    protected $strikingAssignmentThree, $customerAssignmentThree;
    protected $initialSalesActivity;
    protected $salesActivityScheduleOneA;
    protected $salesActivityScheduleOneB;
    protected $salesActivityScheduleTwoA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerJourney')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('StrikingAssignment')->truncate();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();

        $this->sales->columns['role'] = SalesRole::STRIKER->value;
        
        $this->customerOne = new EntityRecord(Customer::class, 1);
        $this->customerOne->columns['City_id'] = $this->city->columns['id'];
        $this->customerOne->columns['status'] = CustomerStatus::STRIKING_REQUIRED;
        $this->customerTwo = new EntityRecord(Customer::class, 2);
        $this->customerTwo->columns['City_id'] = $this->city->columns['id'];
        $this->customerTwo->columns['status'] = CustomerStatus::STRIKING_REQUIRED;
        $this->customerThree = new EntityRecord(Customer::class, 3);
        $this->customerThree->columns['City_id'] = $this->city->columns['id'];
        $this->customerThree->columns['status'] = CustomerStatus::STRIKING_REQUIRED;

        $this->initialSalesActivity = new EntityRecord(SalesActivity::class, 'initial');
        $this->initialSalesActivity->columns['initial'] = true;
        $this->initialSalesActivity->columns['duration'] = 30;
        
        $this->customerJourneyOne = new EntityRecord(CustomerJourney::class, 'One');
        $this->customerJourneyTwo = new EntityRecord(CustomerJourney::class, 'Two');

        $this->customerAssignmentOne = new EntityRecord(CustomerAssignment::class, 'One');
        $this->strikingAssignmentOne = new EntityRecord(StrikingAssignment::class, 'One');
        $this->strikingAssignmentOne->columns['CustomerAssignment_id'] = $this->customerAssignmentOne->columns['id'];
        $this->strikingAssignmentOne->columns['id'] = $this->customerAssignmentOne->columns['id'];
        $this->strikingAssignmentOne->columns['Sales_id'] = $this->sales->columns['id'];
        $this->strikingAssignmentOne->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->strikingAssignmentOne->columns['CustomerJourney_id'] = $this->customerJourneyOne->columns['id'];
        $this->strikingAssignmentOne->columns['status'] = CustomerAssignmentStatus::ACTIVE->value;
        $this->customerAssignmentTwo = new EntityRecord(CustomerAssignment::class, 'Two');
        $this->strikingAssignmentTwo = new EntityRecord(StrikingAssignment::class, 'Two');
        $this->strikingAssignmentTwo->columns['CustomerAssignment_id'] = $this->customerAssignmentTwo->columns['id'];
        $this->strikingAssignmentTwo->columns['id'] = $this->customerAssignmentTwo->columns['id'];
        $this->strikingAssignmentTwo->columns['Sales_id'] = $this->sales->columns['id'];
        $this->strikingAssignmentTwo->columns['Customer_id'] = $this->customerTwo->columns['id'];
        $this->strikingAssignmentTwo->columns['CustomerJourney_id'] = $this->customerJourneyOne->columns['id'];
        $this->strikingAssignmentTwo->columns['status'] = CustomerAssignmentStatus::ACTIVE->value;
        $this->customerAssignmentThree = new EntityRecord(CustomerAssignment::class, 'Three');
        $this->strikingAssignmentThree = new EntityRecord(StrikingAssignment::class, 'Three');
        $this->strikingAssignmentThree->columns['CustomerAssignment_id'] = $this->customerAssignmentThree->columns['id'];
        $this->strikingAssignmentThree->columns['id'] = $this->customerAssignmentThree->columns['id'];
        $this->strikingAssignmentThree->columns['Sales_id'] = $this->sales->columns['id'];
        $this->strikingAssignmentThree->columns['Customer_id'] = $this->customerThree->columns['id'];
        $this->strikingAssignmentThree->columns['CustomerJourney_id'] = $this->customerJourneyOne->columns['id'];
        $this->strikingAssignmentThree->columns['status'] = CustomerAssignmentStatus::ACTIVE->value;
        
        $this->salesActivityScheduleOneA = new EntityRecord(SalesActivitySchedule::class, 'OneA');
        $this->salesActivityScheduleOneA->columns['CustomerAssignment_id'] = $this->strikingAssignmentOne->columns['id'];
        $this->salesActivityScheduleOneA->columns['status'] = SalesActivityScheduleStatus::COMPLETED->value;
        $this->salesActivityScheduleOneB = new EntityRecord(SalesActivitySchedule::class, 'OneB');
        $this->salesActivityScheduleOneB->columns['CustomerAssignment_id'] = $this->strikingAssignmentOne->columns['id'];
        $this->salesActivityScheduleOneB->columns['status'] = SalesActivityScheduleStatus::SCHEDULED->value;
        $this->salesActivityScheduleTwoA = new EntityRecord(SalesActivitySchedule::class, 'TwoA');
        $this->salesActivityScheduleTwoA->columns['CustomerAssignment_id'] = $this->strikingAssignmentTwo->columns['id'];
        $this->salesActivityScheduleTwoA->columns['status'] = SalesActivityScheduleStatus::COMPLETED->value;
        
        $this->customerVerificationOne = new EntityRecord(CustomerVerification::class, 'One');
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerJourney')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('StrikingAssignment')->truncate();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
    }
    
    //
    protected function updateJourney()
    {
        $this->prepareSalesDependency();
        
        $this->customerJourneyOne->insert($this->connection);
        $this->customerJourneyTwo->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->strikingAssignmentOne->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
mutation (
    $id: ID,
    $CustomerJourney_id: ID,
) {
    updateJourney (
        id: $id,
        CustomerJourney_id: $CustomerJourney_id,
    ) {
        customerJourney { name }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->strikingAssignmentOne->columns['id'],
            'CustomerJourney_id' => $this->customerJourneyTwo->columns['id'],
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_updateJourney_200()
    {
$this->disableExceptionHandling();
        $this->updateJourney();
        $this->seeStatusCode(200);

        $this->seeJsonContains([
            'customerJourney' => [
                'name' => $this->customerJourneyTwo->columns['name'],
            ],
        ]);

        $this->seeInDatabase('StrikingAssignment', [
            'id' => $this->strikingAssignmentOne->columns['id'],
            'CustomerJourney_id' => $this->customerJourneyTwo->columns['id'],
        ]);
    }

    //
    protected function viewDetail()
    {
        $this->prepareSalesDependency();
        $this->customerOne->insert($this->connection);
        $this->customerJourneyOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->strikingAssignmentOne->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
query ( $id: ID!) {
    strikingAssignmentDetail ( id: $id ) {
        id, status, createdTime
        customer {
            id, name, email
            city { id, name }
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'salesId' => $this->sales->columns['id'],
            'id' => $this->strikingAssignmentOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_viewDetail_200()
    {
        $this->viewDetail();
        $this->seeJsonContains([
            'id' => $this->strikingAssignmentOne->columns['id'],
            'status' => $this->strikingAssignmentOne->columns['status'],
            'createdTime' => $this->jakartaDateTimeFormat($this->customerAssignmentOne->columns['createdTime']),
            'customer' => [
                'id' => $this->customerOne->columns['id'],
                'name' => $this->customerOne->columns['name'],
                'email' => $this->customerOne->columns['email'],
                'city' => [
                    'id' => $this->city->columns['id'],
                    'name' => $this->city->columns['name'],
                ],
            ],
        ]);
    }
    
    //
    protected function viewList()
    {
        $this->prepareSalesDependency();
        $this->customerJourneyOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerThree->insert($this->connection);

        $this->customerAssignmentOne->insert($this->connection);
        $this->customerAssignmentTwo->insert($this->connection);
        $this->customerAssignmentThree->insert($this->connection);

        $this->strikingAssignmentOne->insert($this->connection);
        $this->strikingAssignmentTwo->insert($this->connection);
        $this->strikingAssignmentThree->insert($this->connection);
        
        $this->salesActivityScheduleOneA->insert($this->connection);
        $this->salesActivityScheduleOneB->insert($this->connection);
        $this->salesActivityScheduleTwoA->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
query ( $filters: [FilterInput] ) {
    strikingAssignmentList ( filters: $filters ) {
        list {
            id, status, createdTime
            customer {
                id, name, email
                city { id, name }
            }
        },
        cursorLimit { total, cursorToNextPage }
    }
}
_QUERY;
        $this->graphqlVariables = $this->getPaginationInput();
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_viewList_200()
    {
        $this->viewList();
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->strikingAssignmentOne->columns['id'],
                    'status' => $this->strikingAssignmentOne->columns['status'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->customerAssignmentOne->columns['createdTime']),
                    'customer' => [
                        'id' => $this->customerOne->columns['id'],
                        'name' => $this->customerOne->columns['name'],
                        'email' => $this->customerOne->columns['email'],
                        'city' => [
                            'id' => $this->city->columns['id'],
                            'name' => $this->city->columns['name'],
                        ],
                    ],
                ],
                [
                    'id' => $this->strikingAssignmentTwo->columns['id'],
                    'status' => $this->strikingAssignmentTwo->columns['status'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->customerAssignmentTwo->columns['createdTime']),
                    'customer' => [
                        'id' => $this->customerTwo->columns['id'],
                        'name' => $this->customerTwo->columns['name'],
                        'email' => $this->customerTwo->columns['email'],
                        'city' => [
                            'id' => $this->city->columns['id'],
                            'name' => $this->city->columns['name'],
                        ],
                    ],
                ],
                [
                    'id' => $this->strikingAssignmentThree->columns['id'],
                    'status' => $this->strikingAssignmentThree->columns['status'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->customerAssignmentThree->columns['createdTime']),
                    'customer' => [
                        'id' => $this->customerThree->columns['id'],
                        'name' => $this->customerThree->columns['name'],
                        'email' => $this->customerThree->columns['email'],
                        'city' => [
                            'id' => $this->city->columns['id'],
                            'name' => $this->city->columns['name'],
                        ],
                    ],
                ],
            ],
            'cursorLimit' => [
                'total' => 3,
                'cursorToNextPage' => null,
            ],
        ]);
    }
    public function test_viewList_activeAssignments_200()
    {
        $this->strikingAssignmentOne->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        $this->filters = [
            ['column' => 'StrikingAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value]
        ];
        $this->viewList();
        $this->seeStatusCode(200);
        $this->seeJsonDoesntContains(['id' => $this->strikingAssignmentOne->columns['id']]);
        $this->seeJsonContains(['id' => $this->strikingAssignmentTwo->columns['id']]);
        $this->seeJsonContains(['id' => $this->strikingAssignmentThree->columns['id']]);
        $this->seeJsonContains(['total' => 2]);
    }
    public function test_viewList_newAssignments_200()
    {
$this->disableExceptionHandling();
        $this->strikingAssignmentOne->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        $this->filters = [
            ['column' => 'StrikingAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
            ['column' => 'hasSalesActivitySchedule', 'value' => false]
        ];
        $this->viewList();
        $this->seeStatusCode(200);
        $this->seeJsonDoesntContains(['id' => $this->strikingAssignmentOne->columns['id']]);
        $this->seeJsonDoesntContains(['id' => $this->strikingAssignmentTwo->columns['id']]);
        $this->seeJsonContains(['id' => $this->strikingAssignmentThree->columns['id']]);
        $this->seeJsonContains(['total' => 1]);
    }
    public function test_viewList_idleAssignments_200()
    {
$this->disableExceptionHandling();
        $this->strikingAssignmentOne->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        $this->filters = [
            ['column' => 'StrikingAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
            ['column' => 'hasSalesActivitySchedule', 'value' => true],
            ['column' => 'hasActiveSalesActivitySchedule', 'value' => false],
        ];
        $this->viewList();
        $this->seeStatusCode(200);
        $this->seeJsonDoesntContains(['id' => $this->strikingAssignmentOne->columns['id']]);
        $this->seeJsonContains(['id' => $this->strikingAssignmentTwo->columns['id']]);
        $this->seeJsonDoesntContains(['id' => $this->strikingAssignmentThree->columns['id']]);
        $this->seeJsonContains(['total' => 1]);
    }

    //
    protected function viewTotalStrikingAssignment()
    {
        $this->prepareSalesDependency();
        $this->customerJourneyOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerThree->insert($this->connection);

        $this->strikingAssignmentOne->insert($this->connection);
        $this->strikingAssignmentTwo->insert($this->connection);
        $this->strikingAssignmentThree->insert($this->connection);
        
        $this->salesActivityScheduleOneA->insert($this->connection);
        $this->salesActivityScheduleOneB->insert($this->connection);
        $this->salesActivityScheduleTwoA->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
query ( $filters: [FilterInput] ) {
    totalStrikingAssignment (filters: $filters)
}
_QUERY;
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_viewTotalStrikingAssignment_200()
    {
        $this->disableExceptionHandling();
        $this->viewTotalStrikingAssignment();
        $this->seeJsonContains(['totalStrikingAssignment' => 3]);
    }
    public function test_viewTotalStrikingAssignment_activeOnly_200()
    {
        $this->strikingAssignmentThree->columns['status'] = CustomerAssignmentStatus::CANCELLED->value;
        $this->graphqlVariables['filters'] = [
            ['column' => 'StrikingAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
        ];
        $this->viewTotalStrikingAssignment();
        $this->seeJsonContains(['totalStrikingAssignment' => 2]);
    }
    public function test_viewTotalStrikingAssignment_completed_200()
    {
        $this->strikingAssignmentOne->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        $this->strikingAssignmentTwo->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        $this->graphqlVariables['filters'] = [
            ['column' => 'StrikingAssignment.status', 'value' => CustomerAssignmentStatus::COMPLETED->value],
        ];
        $this->viewTotalStrikingAssignment();
        $this->seeJsonContains(['totalStrikingAssignment' => 2]);
    }
    public function test_viewTotalStrikingAssignment_newAssignment_200()
    {
        $this->graphqlVariables['filters'] = [
            ['column' => 'StrikingAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
            ['column' => 'hasSalesActivitySchedule', 'value' => false],
        ];
        $this->viewTotalStrikingAssignment();
        $this->seeJsonContains(['totalStrikingAssignment' => 1]);
    }
    public function test_viewTotalStrikingAssignment_idleAssignment_200()
    {
        $this->graphqlVariables['filters'] = [
            ['column' => 'StrikingAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
            ['column' => 'hasSalesActivitySchedule', 'value' => true],
            ['column' => 'hasActiveSalesActivitySchedule', 'value' => false],
        ];
        $this->viewTotalStrikingAssignment();
        $this->seeJsonContains(['totalStrikingAssignment' => 1]);
    }

    //
    public function test_multiRequest_200()
    {
        $this->prepareSalesDependency();
        $this->customerJourneyOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerThree->insert($this->connection);

        $this->strikingAssignmentThree->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        $this->strikingAssignmentOne->insert($this->connection);
        $this->strikingAssignmentTwo->insert($this->connection);
        $this->strikingAssignmentThree->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
query (
    $activeAssignmentFilters: [FilterInput],
    $recycleAssignmentFilters: [FilterInput],
    $goodFundAssignmentFilters: [FilterInput]
) {
    totalStrikingAssignment,
    totalActiveStrikingAssignment: totalStrikingAssignment ( filters: $activeAssignmentFilters ),
    totalRecycleStrikingAssignment: totalStrikingAssignment ( filters: $recycleAssignmentFilters ),
    totalGoodFundStrikingAssignment: totalStrikingAssignment ( filters: $goodFundAssignmentFilters ),
}
_QUERY;
        $this->graphqlVariables = [
            'activeAssignmentFilters' => [
                ['column' => 'StrikingAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
            ],
            'recycleAssignmentFilters' => [
                ['column' => 'StrikingAssignment.status', 'value' => CustomerAssignmentStatus::CANCELLED->value],
            ],
            'goodFundAssignmentFilters' => [
                ['column' => 'StrikingAssignment.status', 'value' => CustomerAssignmentStatus::COMPLETED->value],
            ],
        ];
        $this->postGraphqlRequest($this->sales->token);

        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'totalStrikingAssignment' => 3,
            'totalActiveStrikingAssignment' => 2,
            'totalRecycleStrikingAssignment' => 0,
            'totalGoodFundStrikingAssignment' => 1,
        ]);
    }
}
