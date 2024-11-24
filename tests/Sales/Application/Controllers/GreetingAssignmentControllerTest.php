<?php

namespace Sales\Application\Controllers;

use Company\Domain\Model\Customer;
use Company\Domain\Model\Province\City;
use Company\Domain\Model\SalesActivity;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\FactFindingAssignment;
use Sales\Domain\Model\Sales\GreetingAssignment;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\SalesActivityScheduleStatus;
use Shared\Domain\Enum\SalesRole;
use Tests\Http\Record\EntityRecord;
use Tests\Sales\Application\Controllers\SalesControllerTestCase;

class GreetingAssignmentControllerTest extends SalesControllerTestCase
{

    protected $cityOne;
    protected $customerOne;
    protected $customerTwo;
    protected $greetingAssignmentOne, $customerAssignmentOne;
    protected $greetingAssignmentTwo, $customerAssignmentTwo;
    protected $greetingAssignmentThree, $customerAssignmentThree;
    protected $initialSalesActivity;
    protected $salesActivityScheduleOneA;
    protected $salesActivityScheduleOneB;
    protected $salesActivityScheduleTwoA;

    protected $customerPayload;
    //
    protected $factFinderOne, $factFindingAssignment_11, $customerAssignment_11, $factFindingAssignment_12, $customerAssignment_12;
    protected $factFinderTwo, $factFindingAssignment_21, $customerAssignment_21;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('GreetingAssignment')->truncate();
        $this->connection->table('FactFindingAssignment')->truncate();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();

        $this->sales->columns['role'] = SalesRole::GREETER->value;
        $this->cityOne = new EntityRecord(City::class, 1);
        
        $this->customerOne = new EntityRecord(Customer::class, 1);
        $this->customerOne->columns['City_id'] = $this->city->columns['id'];
        $this->customerTwo = new EntityRecord(Customer::class, 2);
        $this->customerTwo->columns['City_id'] = $this->city->columns['id'];
        $this->customerThree = new EntityRecord(Customer::class, 3);
        $this->customerThree->columns['City_id'] = $this->city->columns['id'];

        $this->initialSalesActivity = new EntityRecord(SalesActivity::class, 'initial');
        $this->initialSalesActivity->columns['initial'] = true;
        $this->initialSalesActivity->columns['duration'] = 30;

        $this->customerAssignmentOne = new EntityRecord(CustomerAssignment::class, 'One');
        $this->greetingAssignmentOne = new EntityRecord(GreetingAssignment::class, 'One');
        $this->greetingAssignmentOne->columns['CustomerAssignment_id'] = $this->customerAssignmentOne->columns['id'];
        $this->greetingAssignmentOne->columns['id'] = $this->customerAssignmentOne->columns['id'];
        $this->greetingAssignmentOne->columns['Sales_id'] = $this->sales->columns['id'];
        $this->greetingAssignmentOne->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->greetingAssignmentOne->columns['status'] = CustomerAssignmentStatus::ACTIVE->value;
        $this->customerAssignmentTwo = new EntityRecord(CustomerAssignment::class, 'Two');
        $this->greetingAssignmentTwo = new EntityRecord(GreetingAssignment::class, 'Two');
        $this->greetingAssignmentTwo->columns['CustomerAssignment_id'] = $this->customerAssignmentTwo->columns['id'];
        $this->greetingAssignmentTwo->columns['id'] = $this->customerAssignmentTwo->columns['id'];
        $this->greetingAssignmentTwo->columns['Sales_id'] = $this->sales->columns['id'];
        $this->greetingAssignmentTwo->columns['Customer_id'] = $this->customerTwo->columns['id'];
        $this->greetingAssignmentTwo->columns['status'] = CustomerAssignmentStatus::ACTIVE->value;
        $this->customerAssignmentThree = new EntityRecord(CustomerAssignment::class, 'Three');
        $this->greetingAssignmentThree = new EntityRecord(GreetingAssignment::class, 'Three');
        $this->greetingAssignmentThree->columns['CustomerAssignment_id'] = $this->customerAssignmentThree->columns['id'];
        $this->greetingAssignmentThree->columns['id'] = $this->customerAssignmentThree->columns['id'];
        $this->greetingAssignmentThree->columns['Sales_id'] = $this->sales->columns['id'];
        $this->greetingAssignmentThree->columns['Customer_id'] = $this->customerThree->columns['id'];
        $this->greetingAssignmentThree->columns['status'] = CustomerAssignmentStatus::ACTIVE->value;
        
        $this->salesActivityScheduleOneA = new EntityRecord(SalesActivitySchedule::class, 'OneA');
        $this->salesActivityScheduleOneA->columns['CustomerAssignment_id'] = $this->greetingAssignmentOne->columns['id'];
        $this->salesActivityScheduleOneA->columns['status'] = SalesActivityScheduleStatus::COMPLETED->value;
        $this->salesActivityScheduleOneB = new EntityRecord(SalesActivitySchedule::class, 'OneB');
        $this->salesActivityScheduleOneB->columns['CustomerAssignment_id'] = $this->greetingAssignmentOne->columns['id'];
        $this->salesActivityScheduleOneB->columns['status'] = SalesActivityScheduleStatus::SCHEDULED->value;
        $this->salesActivityScheduleTwoA = new EntityRecord(SalesActivitySchedule::class, 'TwoA');
        $this->salesActivityScheduleTwoA->columns['CustomerAssignment_id'] = $this->greetingAssignmentTwo->columns['id'];
        $this->salesActivityScheduleTwoA->columns['status'] = SalesActivityScheduleStatus::COMPLETED->value;
        
        $this->customerPayload = [
            'City_id' => $this->cityOne->columns['id'],
            'name' => 'new customer name',
            'email' => 'newAddress@email.org',
        ];
        
        //
        $this->factFinderOne = new EntityRecord(Sales::class, 'One');
        $this->factFinderOne->columns['Manager_id'] = $this->manager->columns['id'];
        $this->factFinderOne->columns['role'] = SalesRole::FACT_FINDER->value;
        $this->factFinderTwo = new EntityRecord(Sales::class, 'Two');
        $this->factFinderTwo->columns['Manager_id'] = $this->manager->columns['id'];
        $this->factFinderTwo->columns['role'] = SalesRole::FACT_FINDER->value;
        
        $this->customerAssignment_11 = new EntityRecord(CustomerAssignment::class, '11');
        $this->factFindingAssignment_11 = new EntityRecord(FactFindingAssignment::class, '11');
        $this->factFindingAssignment_11->columns['CustomerAssignment_id'] = $this->customerAssignment_11->columns['id'];
        $this->factFindingAssignment_11->columns['id'] = $this->customerAssignment_11->columns['id'];
        $this->factFindingAssignment_11->columns['Sales_id'] = $this->factFinderOne->columns['id'];
        $this->customerAssignment_12 = new EntityRecord(CustomerAssignment::class, '12');
        $this->factFindingAssignment_12 = new EntityRecord(FactFindingAssignment::class, '12');
        $this->factFindingAssignment_12->columns['CustomerAssignment_id'] = $this->customerAssignment_12->columns['id'];
        $this->factFindingAssignment_12->columns['id'] = $this->customerAssignment_12->columns['id'];
        $this->factFindingAssignment_12->columns['Sales_id'] = $this->factFinderOne->columns['id'];
        $this->customerAssignment_21 = new EntityRecord(CustomerAssignment::class, '21');
        $this->factFindingAssignment_21 = new EntityRecord(FactFindingAssignment::class, '21');
        $this->factFindingAssignment_21->columns['CustomerAssignment_id'] = $this->customerAssignment_21->columns['id'];
        $this->factFindingAssignment_21->columns['id'] = $this->customerAssignment_21->columns['id'];
        $this->factFindingAssignment_21->columns['Sales_id'] = $this->factFinderTwo->columns['id'];
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('GreetingAssignment')->truncate();
        $this->connection->table('FactFindingAssignment')->truncate();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
    }

    //
    protected function updateCustomerBio()
    {
        $this->prepareSalesDependency();

        $this->cityOne->insert($this->connection);
        $this->customerOne->insert($this->connection);

        $this->customerAssignmentOne->insert($this->connection);
        $this->greetingAssignmentOne->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID, $customer: CustomerInput ) {
    updateCustomerBio ( id: $id, customer: $customer ) {
        id, customer { name, email, source, city { id } }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->greetingAssignmentOne->columns['id'],
            'customer' => $this->customerPayload,
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_udpateCustomerBio_200()
    {
$this->disableExceptionHandling();
        $this->updateCustomerBio();
        $this->seeStatusCode(200);

        $this->seeJsonContains([
            'id' => $this->greetingAssignmentOne->columns['id'],
            'customer' => [
                'name' => $this->customerPayload['name'],
                'email' => $this->customerPayload['email'],
                'source' => $this->customerOne->columns['source'],
                'city' => [
                    'id' => $this->customerPayload['City_id'],
                ],
            ],
        ]);

        $this->seeInDatabase('Customer', [
            'id' => $this->greetingAssignmentOne->columns['Customer_id'],
            'name' => $this->customerPayload['name'],
            'email' => $this->customerPayload['email'],
            'source' => $this->customerOne->columns['source'],
            'City_id' => $this->customerPayload['City_id'],
        ]);
    }

    //
    protected function validateCustomer()
    {
        $this->prepareSalesDependency();

        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->greetingAssignmentOne->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID) {
    validateCustomer ( id: $id ) {
        id, status
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->greetingAssignmentOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_validateCustomer_200()
    {
$this->disableExceptionHandling();
        $this->validateCustomer();
        $this->seeStatusCode(200);

        $this->seeJsonContains([
            'id' => $this->greetingAssignmentOne->columns['id'],
            'status' => CustomerAssignmentStatus::COMPLETED->value,
        ]);

        $this->seeInDatabase('GreetingAssignment', [
            'id' => $this->greetingAssignmentOne->columns['id'],
            'status' => CustomerAssignmentStatus::COMPLETED->value,
        ]);
        $this->seeInDatabase('Customer', [
            'id' => $this->greetingAssignmentOne->columns['Customer_id'],
            'status' => \Shared\Domain\Enum\CustomerStatus::FACT_FINDING_REQUIRED->value,
        ]);
    }
    public function test_validateCustomer_handoverToLeastOccupiedFactFinder()
    {
        $this->factFinderOne->insert($this->connection);
        $this->factFinderTwo->insert($this->connection);
        
        $this->customerAssignment_11->insert($this->connection);
        $this->customerAssignment_12->insert($this->connection);
        $this->customerAssignment_21->insert($this->connection);
        $this->factFindingAssignment_11->insert($this->connection);
        $this->factFindingAssignment_12->insert($this->connection);
        $this->factFindingAssignment_21->insert($this->connection);
        
        $this->validateCustomer();
        $this->seeStatusCode(200);
        
        $this->seeInDatabase('FactFindingAssignment', [
            'Customer_id' => $this->greetingAssignmentOne->columns['Customer_id'],
            'Sales_id' => $this->factFinderTwo->columns['id'],
            'status' => CustomerAssignmentStatus::ACTIVE->value,
        ]);
    }

    //
    protected function recycleCustomer()
    {
        $this->prepareSalesDependency();

        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->greetingAssignmentOne->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID) {
    recycleCustomer ( id: $id ) {
        id, status
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->greetingAssignmentOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_recycleCustomer_200()
    {
$this->disableExceptionHandling();
        $this->recycleCustomer();
        $this->seeStatusCode(200);

        $this->seeJsonContains([
            'id' => $this->greetingAssignmentOne->columns['id'],
            'status' => CustomerAssignmentStatus::COMPLETED->value,
        ]);

        $this->seeInDatabase('GreetingAssignment', [
            'id' => $this->greetingAssignmentOne->columns['id'],
            'status' => CustomerAssignmentStatus::COMPLETED->value,
        ]);
        $this->seeInDatabase('Customer', [
            'id' => $this->greetingAssignmentOne->columns['Customer_id'],
            'status' => \Shared\Domain\Enum\CustomerStatus::RECYCLED->value,
        ]);
    }

    //
    protected function viewDetail()
    {
        $this->prepareSalesDependency();
        $this->customerOne->insert($this->connection);

        $this->customerAssignmentOne->insert($this->connection);
        $this->greetingAssignmentOne->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
query ( $id: ID!) {
    greetingAssignmentDetail ( id: $id ) {
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
            'id' => $this->greetingAssignmentOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_viewDetail_200()
    {
        $this->viewDetail();
        $this->seeJsonContains([
            'id' => $this->greetingAssignmentOne->columns['id'],
            'status' => $this->greetingAssignmentOne->columns['status'],
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

        $this->greetingAssignmentOne->insert($this->connection);
        $this->greetingAssignmentTwo->insert($this->connection);
        $this->greetingAssignmentThree->insert($this->connection);
        
        $this->salesActivityScheduleOneA->insert($this->connection);
        $this->salesActivityScheduleOneB->insert($this->connection);
        $this->salesActivityScheduleTwoA->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
query ( $filters: [FilterInput] ) {
    greetingAssignmentList ( filters: $filters ) {
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
                    'id' => $this->greetingAssignmentOne->columns['id'],
                    'status' => $this->greetingAssignmentOne->columns['status'],
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
                    'id' => $this->greetingAssignmentTwo->columns['id'],
                    'status' => $this->greetingAssignmentTwo->columns['status'],
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
                    'id' => $this->greetingAssignmentThree->columns['id'],
                    'status' => $this->greetingAssignmentThree->columns['status'],
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
        $this->greetingAssignmentOne->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        $this->filters = [
            ['column' => 'GreetingAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value]
        ];
        $this->viewList();
        $this->seeStatusCode(200);
        $this->seeJsonDoesntContains(['id' => $this->greetingAssignmentOne->columns['id']]);
        $this->seeJsonContains(['id' => $this->greetingAssignmentTwo->columns['id']]);
        $this->seeJsonContains(['id' => $this->greetingAssignmentThree->columns['id']]);
        $this->seeJsonContains(['total' => 2]);
    }
    public function test_viewList_newAssignments_200()
    {
$this->disableExceptionHandling();
        $this->greetingAssignmentOne->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        $this->filters = [
            ['column' => 'GreetingAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
            ['column' => 'hasSalesActivitySchedule', 'value' => false]
        ];
        $this->viewList();
        $this->seeStatusCode(200);
        $this->seeJsonDoesntContains(['id' => $this->greetingAssignmentOne->columns['id']]);
        $this->seeJsonDoesntContains(['id' => $this->greetingAssignmentTwo->columns['id']]);
        $this->seeJsonContains(['id' => $this->greetingAssignmentThree->columns['id']]);
        $this->seeJsonContains(['total' => 1]);
    }
    public function test_viewList_idleAssignments_200()
    {
$this->disableExceptionHandling();
        $this->greetingAssignmentOne->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        $this->filters = [
            ['column' => 'GreetingAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
            ['column' => 'hasSalesActivitySchedule', 'value' => true],
            ['column' => 'hasActiveSalesActivitySchedule', 'value' => false],
        ];
        $this->viewList();
        $this->seeStatusCode(200);
        $this->seeJsonDoesntContains(['id' => $this->greetingAssignmentOne->columns['id']]);
        $this->seeJsonContains(['id' => $this->greetingAssignmentTwo->columns['id']]);
        $this->seeJsonDoesntContains(['id' => $this->greetingAssignmentThree->columns['id']]);
        $this->seeJsonContains(['total' => 1]);
    }

    //
    protected function viewTotalGreetingAssignment()
    {
        $this->prepareSalesDependency();
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerThree->insert($this->connection);

        $this->greetingAssignmentOne->insert($this->connection);
        $this->greetingAssignmentTwo->insert($this->connection);
        $this->greetingAssignmentThree->insert($this->connection);
        
        $this->salesActivityScheduleOneA->insert($this->connection);
        $this->salesActivityScheduleOneB->insert($this->connection);
        $this->salesActivityScheduleTwoA->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
query ( $filters: [FilterInput] ) {
    totalGreetingAssignment (filters: $filters)
}
_QUERY;
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_viewTotalGreetingAssignment_200()
    {
        $this->disableExceptionHandling();
        $this->viewTotalGreetingAssignment();
        $this->seeJsonContains(['totalGreetingAssignment' => 3]);
    }
    public function test_viewTotalGreetingAssignment_activeOnly_200()
    {
        $this->greetingAssignmentThree->columns['status'] = CustomerAssignmentStatus::CANCELLED->value;
        $this->graphqlVariables['filters'] = [
            ['column' => 'GreetingAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
        ];
        $this->viewTotalGreetingAssignment();
        $this->seeJsonContains(['totalGreetingAssignment' => 2]);
    }
    public function test_viewTotalGreetingAssignment_completed_200()
    {
        $this->greetingAssignmentOne->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        $this->greetingAssignmentTwo->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        $this->graphqlVariables['filters'] = [
            ['column' => 'GreetingAssignment.status', 'value' => CustomerAssignmentStatus::COMPLETED->value],
        ];
        $this->viewTotalGreetingAssignment();
        $this->seeJsonContains(['totalGreetingAssignment' => 2]);
    }
    public function test_viewTotalGreetingAssignment_newAssignment_200()
    {
        $this->graphqlVariables['filters'] = [
            ['column' => 'GreetingAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
            ['column' => 'hasSalesActivitySchedule', 'value' => false],
        ];
        $this->viewTotalGreetingAssignment();
        $this->seeJsonContains(['totalGreetingAssignment' => 1]);
    }
    public function test_viewTotalGreetingAssignment_idleAssignment_200()
    {
        $this->graphqlVariables['filters'] = [
            ['column' => 'GreetingAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
            ['column' => 'hasSalesActivitySchedule', 'value' => true],
            ['column' => 'hasActiveSalesActivitySchedule', 'value' => false],
        ];
        $this->viewTotalGreetingAssignment();
        $this->seeJsonContains(['totalGreetingAssignment' => 1]);
    }

    //
    public function test_multiRequest_200()
    {
        $this->prepareSalesDependency();
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerThree->insert($this->connection);

        $this->greetingAssignmentThree->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        $this->greetingAssignmentOne->insert($this->connection);
        $this->greetingAssignmentTwo->insert($this->connection);
        $this->greetingAssignmentThree->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
query (
    $activeAssignmentFilters: [FilterInput],
    $recycleAssignmentFilters: [FilterInput],
    $goodFundAssignmentFilters: [FilterInput]
) {
    totalGreetingAssignment,
    totalActiveGreetingAssignment: totalGreetingAssignment ( filters: $activeAssignmentFilters ),
    totalRecycleGreetingAssignment: totalGreetingAssignment ( filters: $recycleAssignmentFilters ),
    totalGoodFundGreetingAssignment: totalGreetingAssignment ( filters: $goodFundAssignmentFilters ),
}
_QUERY;
        $this->graphqlVariables = [
            'activeAssignmentFilters' => [
                ['column' => 'GreetingAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
            ],
            'recycleAssignmentFilters' => [
                ['column' => 'GreetingAssignment.status', 'value' => CustomerAssignmentStatus::CANCELLED->value],
            ],
            'goodFundAssignmentFilters' => [
                ['column' => 'GreetingAssignment.status', 'value' => CustomerAssignmentStatus::COMPLETED->value],
            ],
        ];
        $this->postGraphqlRequest($this->sales->token);

        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'totalGreetingAssignment' => 3,
            'totalActiveGreetingAssignment' => 2,
            'totalRecycleGreetingAssignment' => 0,
            'totalGoodFundGreetingAssignment' => 1,
        ]);
    }
}
