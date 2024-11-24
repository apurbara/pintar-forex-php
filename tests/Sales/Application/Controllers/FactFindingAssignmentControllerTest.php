<?php

namespace Sales\Application\Controllers;

use Company\Domain\Model\Customer;
use Company\Domain\Model\CustomerVerification;
use Company\Domain\Model\Province\City;
use Company\Domain\Model\SalesActivity;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\FactFindingAssignment;
use Sales\Domain\Model\Sales\StrikingAssignment;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\CustomerStatus;
use Shared\Domain\Enum\SalesActivityScheduleStatus;
use Shared\Domain\Enum\SalesRole;
use Tests\Http\Record\EntityRecord;
use Tests\Sales\Application\Controllers\SalesControllerTestCase;

class FactFindingAssignmentControllerTest extends SalesControllerTestCase
{

    protected $customerOne;
    protected $customerTwo;
    protected $factFindingAssignmentOne, $customerAssignmentOne;
    protected $factFindingAssignmentTwo, $customerAssignmentTwo;
    protected $factFindingAssignmentThree, $customerAssignmentThree;
    protected $initialSalesActivity;
    protected $salesActivityScheduleOneA;
    protected $salesActivityScheduleOneB;
    protected $salesActivityScheduleTwoA;
    protected $customerVerificationOne;
    
    protected $customerVerificationReportPayload;
    //
    protected $strikerOne, $strikingAssignment_11, $customerAssignment_11, $strikingAssignment_12, $customerAssignment_12;
    protected $strikerTwo, $strikingAssignment_21, $customerAssignment_21;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerVerification')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('FactFindingAssignment')->truncate();
        $this->connection->table('StrikingAssignment')->truncate();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();

        $this->sales->columns['role'] = SalesRole::FACT_FINDER->value;
        
        $this->customerOne = new EntityRecord(Customer::class, 1);
        $this->customerOne->columns['City_id'] = $this->city->columns['id'];
        $this->customerOne->columns['status'] = CustomerStatus::FACT_FINDING_REQUIRED;
        $this->customerTwo = new EntityRecord(Customer::class, 2);
        $this->customerTwo->columns['City_id'] = $this->city->columns['id'];
        $this->customerTwo->columns['status'] = CustomerStatus::FACT_FINDING_REQUIRED;
        $this->customerThree = new EntityRecord(Customer::class, 3);
        $this->customerThree->columns['City_id'] = $this->city->columns['id'];
        $this->customerThree->columns['status'] = CustomerStatus::FACT_FINDING_REQUIRED;

        $this->initialSalesActivity = new EntityRecord(SalesActivity::class, 'initial');
        $this->initialSalesActivity->columns['initial'] = true;
        $this->initialSalesActivity->columns['duration'] = 30;

        $this->customerAssignmentOne = new EntityRecord(CustomerAssignment::class, 'One');
        $this->factFindingAssignmentOne = new EntityRecord(FactFindingAssignment::class, 'One');
        $this->factFindingAssignmentOne->columns['CustomerAssignment_id'] = $this->customerAssignmentOne->columns['id'];
        $this->factFindingAssignmentOne->columns['id'] = $this->customerAssignmentOne->columns['id'];
        $this->factFindingAssignmentOne->columns['Sales_id'] = $this->sales->columns['id'];
        $this->factFindingAssignmentOne->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->factFindingAssignmentOne->columns['status'] = CustomerAssignmentStatus::ACTIVE->value;
        $this->customerAssignmentTwo = new EntityRecord(CustomerAssignment::class, 'Two');
        $this->factFindingAssignmentTwo = new EntityRecord(FactFindingAssignment::class, 'Two');
        $this->factFindingAssignmentTwo->columns['CustomerAssignment_id'] = $this->customerAssignmentTwo->columns['id'];
        $this->factFindingAssignmentTwo->columns['id'] = $this->customerAssignmentTwo->columns['id'];
        $this->factFindingAssignmentTwo->columns['Sales_id'] = $this->sales->columns['id'];
        $this->factFindingAssignmentTwo->columns['Customer_id'] = $this->customerTwo->columns['id'];
        $this->factFindingAssignmentTwo->columns['status'] = CustomerAssignmentStatus::ACTIVE->value;
        $this->customerAssignmentThree = new EntityRecord(CustomerAssignment::class, 'Three');
        $this->factFindingAssignmentThree = new EntityRecord(FactFindingAssignment::class, 'Three');
        $this->factFindingAssignmentThree->columns['CustomerAssignment_id'] = $this->customerAssignmentThree->columns['id'];
        $this->factFindingAssignmentThree->columns['id'] = $this->customerAssignmentThree->columns['id'];
        $this->factFindingAssignmentThree->columns['Sales_id'] = $this->sales->columns['id'];
        $this->factFindingAssignmentThree->columns['Customer_id'] = $this->customerThree->columns['id'];
        $this->factFindingAssignmentThree->columns['status'] = CustomerAssignmentStatus::ACTIVE->value;
        
        $this->salesActivityScheduleOneA = new EntityRecord(SalesActivitySchedule::class, 'OneA');
        $this->salesActivityScheduleOneA->columns['CustomerAssignment_id'] = $this->factFindingAssignmentOne->columns['id'];
        $this->salesActivityScheduleOneA->columns['status'] = SalesActivityScheduleStatus::COMPLETED->value;
        $this->salesActivityScheduleOneB = new EntityRecord(SalesActivitySchedule::class, 'OneB');
        $this->salesActivityScheduleOneB->columns['CustomerAssignment_id'] = $this->factFindingAssignmentOne->columns['id'];
        $this->salesActivityScheduleOneB->columns['status'] = SalesActivityScheduleStatus::SCHEDULED->value;
        $this->salesActivityScheduleTwoA = new EntityRecord(SalesActivitySchedule::class, 'TwoA');
        $this->salesActivityScheduleTwoA->columns['CustomerAssignment_id'] = $this->factFindingAssignmentTwo->columns['id'];
        $this->salesActivityScheduleTwoA->columns['status'] = SalesActivityScheduleStatus::COMPLETED->value;
        
        $this->customerVerificationOne = new EntityRecord(CustomerVerification::class, 'One');
        
        $this->customerVerificationReportPayload = [
            'FactFindingAssignment_id' => $this->factFindingAssignmentOne->columns['id'],
            'CustomerVerification_id' => $this->customerVerificationOne->columns['id'],
            'note' => 'verification note',
        ];
        
        //
        $this->strikerOne = new EntityRecord(Sales::class, 'One');
        $this->strikerOne->columns['Manager_id'] = $this->manager->columns['id'];
        $this->strikerOne->columns['role'] = SalesRole::STRIKER->value;
        $this->strikerTwo = new EntityRecord(Sales::class, 'Two');
        $this->strikerTwo->columns['Manager_id'] = $this->manager->columns['id'];
        $this->strikerTwo->columns['role'] = SalesRole::STRIKER->value;
        
        $this->customerAssignment_11 = new EntityRecord(CustomerAssignment::class, '11');
        $this->strikingAssignment_11 = new EntityRecord(StrikingAssignment::class, '11');
        $this->strikingAssignment_11->columns['CustomerAssignment_id'] = $this->customerAssignment_11->columns['id'];
        $this->strikingAssignment_11->columns['id'] = $this->customerAssignment_11->columns['id'];
        $this->strikingAssignment_11->columns['Sales_id'] = $this->strikerOne->columns['id'];
        $this->customerAssignment_12 = new EntityRecord(CustomerAssignment::class, '12');
        $this->strikingAssignment_12 = new EntityRecord(StrikingAssignment::class, '12');
        $this->strikingAssignment_12->columns['CustomerAssignment_id'] = $this->customerAssignment_12->columns['id'];
        $this->strikingAssignment_12->columns['id'] = $this->customerAssignment_12->columns['id'];
        $this->strikingAssignment_12->columns['Sales_id'] = $this->strikerOne->columns['id'];
        $this->customerAssignment_21 = new EntityRecord(CustomerAssignment::class, '21');
        $this->strikingAssignment_21 = new EntityRecord(StrikingAssignment::class, '21');
        $this->strikingAssignment_21->columns['CustomerAssignment_id'] = $this->customerAssignment_21->columns['id'];
        $this->strikingAssignment_21->columns['id'] = $this->customerAssignment_21->columns['id'];
        $this->strikingAssignment_21->columns['Sales_id'] = $this->strikerTwo->columns['id'];
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('CustomerVerification')->truncate();
        $this->connection->table('FactFindingAssignment')->truncate();
        $this->connection->table('StrikingAssignment')->truncate();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
    }
    
    //
    protected function submitCustomerVerificationReport()
    {
        $this->prepareSalesDependency();
        
        $this->customerVerificationOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->factFindingAssignmentOne->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
mutation (
    $FactFindingAssignment_id: ID,
    $CustomerVerification_id: ID,
    $note: String,
) {
    submitCustomerVerificationReport (
        FactFindingAssignment_id: $FactFindingAssignment_id,
        CustomerVerification_id: $CustomerVerification_id,
        note: $note,
    ) {
        CustomerVerification_id, note
    }
}
_QUERY;
        $this->graphqlVariables = $this->customerVerificationReportPayload;
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_submitCustomerVerificationReport_200()
    {
$this->disableExceptionHandling();
        $this->submitCustomerVerificationReport();
        $this->seeStatusCode(200);

        $this->seeJsonContains([
            'CustomerVerification_id' => $this->customerVerificationOne->columns['id'],
            'note' => $this->customerVerificationReportPayload['note'],
        ]);

        $this->seeInDatabase('VerificationReport', [
            'Customer_id' => $this->factFindingAssignmentOne->columns['Customer_id'],
            'CustomerVerification_id' => $this->customerVerificationOne->columns['id'],
            'note' => $this->customerVerificationReportPayload['note'],
        ]);
    }

    //
    protected function markCustomerVerified()
    {
        $this->prepareSalesDependency();

        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->factFindingAssignmentOne->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID) {
    markCustomerVerified ( id: $id ) {
        id, status
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->factFindingAssignmentOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_markCustomerVerified_200()
    {
$this->disableExceptionHandling();
        $this->markCustomerVerified();
        $this->seeStatusCode(200);

        $this->seeJsonContains([
            'id' => $this->factFindingAssignmentOne->columns['id'],
            'status' => CustomerAssignmentStatus::COMPLETED->value,
        ]);

        $this->seeInDatabase('FactFindingAssignment', [
            'id' => $this->factFindingAssignmentOne->columns['id'],
            'status' => CustomerAssignmentStatus::COMPLETED->value,
        ]);
        $this->seeInDatabase('Customer', [
            'id' => $this->factFindingAssignmentOne->columns['Customer_id'],
            'status' => CustomerStatus::STRIKING_REQUIRED->value,
        ]);
    }
    public function test_markCustomerVerified_handoverToLeastOccupiedFactFinder()
    {
        $this->strikerOne->insert($this->connection);
        $this->strikerTwo->insert($this->connection);
        
        $this->customerAssignment_11->insert($this->connection);
        $this->customerAssignment_12->insert($this->connection);
        $this->customerAssignment_21->insert($this->connection);
        $this->strikingAssignment_11->insert($this->connection);
        $this->strikingAssignment_12->insert($this->connection);
        $this->strikingAssignment_21->insert($this->connection);
        
        $this->markCustomerVerified();
        $this->seeStatusCode(200);
        
        $this->seeInDatabase('StrikingAssignment', [
            'Customer_id' => $this->factFindingAssignmentOne->columns['Customer_id'],
            'Sales_id' => $this->strikerTwo->columns['id'],
            'status' => CustomerAssignmentStatus::ACTIVE->value,
        ]);
    }

    //
    protected function viewDetail()
    {
        $this->prepareSalesDependency();
        $this->customerOne->insert($this->connection);

        $this->customerAssignmentOne->insert($this->connection);
        $this->factFindingAssignmentOne->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
query ( $id: ID!) {
    factFindingAssignmentDetail ( id: $id ) {
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
            'id' => $this->factFindingAssignmentOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_viewDetail_200()
    {
        $this->viewDetail();
        $this->seeJsonContains([
            'id' => $this->factFindingAssignmentOne->columns['id'],
            'status' => $this->factFindingAssignmentOne->columns['status'],
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
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerThree->insert($this->connection);

        $this->customerAssignmentOne->insert($this->connection);
        $this->customerAssignmentTwo->insert($this->connection);
        $this->customerAssignmentThree->insert($this->connection);

        $this->factFindingAssignmentOne->insert($this->connection);
        $this->factFindingAssignmentTwo->insert($this->connection);
        $this->factFindingAssignmentThree->insert($this->connection);
        
        $this->salesActivityScheduleOneA->insert($this->connection);
        $this->salesActivityScheduleOneB->insert($this->connection);
        $this->salesActivityScheduleTwoA->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
query ( $filters: [FilterInput] ) {
    factFindingAssignmentList ( filters: $filters ) {
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
                    'id' => $this->factFindingAssignmentOne->columns['id'],
                    'status' => $this->factFindingAssignmentOne->columns['status'],
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
                    'id' => $this->factFindingAssignmentTwo->columns['id'],
                    'status' => $this->factFindingAssignmentTwo->columns['status'],
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
                    'id' => $this->factFindingAssignmentThree->columns['id'],
                    'status' => $this->factFindingAssignmentThree->columns['status'],
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
        $this->factFindingAssignmentOne->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        $this->filters = [
            ['column' => 'FactFindingAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value]
        ];
        $this->viewList();
        $this->seeStatusCode(200);
        $this->seeJsonDoesntContains(['id' => $this->factFindingAssignmentOne->columns['id']]);
        $this->seeJsonContains(['id' => $this->factFindingAssignmentTwo->columns['id']]);
        $this->seeJsonContains(['id' => $this->factFindingAssignmentThree->columns['id']]);
        $this->seeJsonContains(['total' => 2]);
    }
    public function test_viewList_newAssignments_200()
    {
$this->disableExceptionHandling();
        $this->factFindingAssignmentOne->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        $this->filters = [
            ['column' => 'FactFindingAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
            ['column' => 'hasSalesActivitySchedule', 'value' => false]
        ];
        $this->viewList();
        $this->seeStatusCode(200);
        $this->seeJsonDoesntContains(['id' => $this->factFindingAssignmentOne->columns['id']]);
        $this->seeJsonDoesntContains(['id' => $this->factFindingAssignmentTwo->columns['id']]);
        $this->seeJsonContains(['id' => $this->factFindingAssignmentThree->columns['id']]);
        $this->seeJsonContains(['total' => 1]);
    }
    public function test_viewList_idleAssignments_200()
    {
$this->disableExceptionHandling();
        $this->factFindingAssignmentOne->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        $this->filters = [
            ['column' => 'FactFindingAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
            ['column' => 'hasSalesActivitySchedule', 'value' => true],
            ['column' => 'hasActiveSalesActivitySchedule', 'value' => false],
        ];
        $this->viewList();
        $this->seeStatusCode(200);
        $this->seeJsonDoesntContains(['id' => $this->factFindingAssignmentOne->columns['id']]);
        $this->seeJsonContains(['id' => $this->factFindingAssignmentTwo->columns['id']]);
        $this->seeJsonDoesntContains(['id' => $this->factFindingAssignmentThree->columns['id']]);
        $this->seeJsonContains(['total' => 1]);
    }

    //
    protected function viewTotalFactFindingAssignment()
    {
        $this->prepareSalesDependency();
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerThree->insert($this->connection);

        $this->factFindingAssignmentOne->insert($this->connection);
        $this->factFindingAssignmentTwo->insert($this->connection);
        $this->factFindingAssignmentThree->insert($this->connection);
        
        $this->salesActivityScheduleOneA->insert($this->connection);
        $this->salesActivityScheduleOneB->insert($this->connection);
        $this->salesActivityScheduleTwoA->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
query ( $filters: [FilterInput] ) {
    totalFactFindingAssignment (filters: $filters)
}
_QUERY;
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_viewTotalFactFindingAssignment_200()
    {
        $this->disableExceptionHandling();
        $this->viewTotalFactFindingAssignment();
        $this->seeJsonContains(['totalFactFindingAssignment' => 3]);
    }
    public function test_viewTotalFactFindingAssignment_activeOnly_200()
    {
        $this->factFindingAssignmentThree->columns['status'] = CustomerAssignmentStatus::CANCELLED->value;
        $this->graphqlVariables['filters'] = [
            ['column' => 'FactFindingAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
        ];
        $this->viewTotalFactFindingAssignment();
        $this->seeJsonContains(['totalFactFindingAssignment' => 2]);
    }
    public function test_viewTotalFactFindingAssignment_completed_200()
    {
        $this->factFindingAssignmentOne->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        $this->factFindingAssignmentTwo->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        $this->graphqlVariables['filters'] = [
            ['column' => 'FactFindingAssignment.status', 'value' => CustomerAssignmentStatus::COMPLETED->value],
        ];
        $this->viewTotalFactFindingAssignment();
        $this->seeJsonContains(['totalFactFindingAssignment' => 2]);
    }
    public function test_viewTotalFactFindingAssignment_newAssignment_200()
    {
        $this->graphqlVariables['filters'] = [
            ['column' => 'FactFindingAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
            ['column' => 'hasSalesActivitySchedule', 'value' => false],
        ];
        $this->viewTotalFactFindingAssignment();
        $this->seeJsonContains(['totalFactFindingAssignment' => 1]);
    }
    public function test_viewTotalFactFindingAssignment_idleAssignment_200()
    {
        $this->graphqlVariables['filters'] = [
            ['column' => 'FactFindingAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
            ['column' => 'hasSalesActivitySchedule', 'value' => true],
            ['column' => 'hasActiveSalesActivitySchedule', 'value' => false],
        ];
        $this->viewTotalFactFindingAssignment();
        $this->seeJsonContains(['totalFactFindingAssignment' => 1]);
    }

    //
    public function test_multiRequest_200()
    {
        $this->prepareSalesDependency();
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerThree->insert($this->connection);

        $this->factFindingAssignmentThree->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        $this->factFindingAssignmentOne->insert($this->connection);
        $this->factFindingAssignmentTwo->insert($this->connection);
        $this->factFindingAssignmentThree->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
query (
    $activeAssignmentFilters: [FilterInput],
    $recycleAssignmentFilters: [FilterInput],
    $goodFundAssignmentFilters: [FilterInput]
) {
    totalFactFindingAssignment,
    totalActiveFactFindingAssignment: totalFactFindingAssignment ( filters: $activeAssignmentFilters ),
    totalRecycleFactFindingAssignment: totalFactFindingAssignment ( filters: $recycleAssignmentFilters ),
    totalGoodFundFactFindingAssignment: totalFactFindingAssignment ( filters: $goodFundAssignmentFilters ),
}
_QUERY;
        $this->graphqlVariables = [
            'activeAssignmentFilters' => [
                ['column' => 'FactFindingAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
            ],
            'recycleAssignmentFilters' => [
                ['column' => 'FactFindingAssignment.status', 'value' => CustomerAssignmentStatus::CANCELLED->value],
            ],
            'goodFundAssignmentFilters' => [
                ['column' => 'FactFindingAssignment.status', 'value' => CustomerAssignmentStatus::COMPLETED->value],
            ],
        ];
        $this->postGraphqlRequest($this->sales->token);

        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'totalFactFindingAssignment' => 3,
            'totalActiveFactFindingAssignment' => 2,
            'totalRecycleFactFindingAssignment' => 0,
            'totalGoodFundFactFindingAssignment' => 1,
        ]);
    }
}
