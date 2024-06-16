<?php

namespace App\Http\Controllers\SalesBC\BySales;

use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Company\Domain\Model\SalesActivity;
use Sales\Domain\DependencyModel\AreaStructure\Area\Customer;
use Sales\Domain\Model\Sales\CustomerAssignment;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use SharedContext\Domain\Enum\SalesActivityScheduleStatus;
use Tests\Http\GraphQL\SalesBC\SalesBCTestCase;
use Tests\Http\Record\EntityRecord;

class CustomerAssignmentControllerTest extends SalesBCTestCase
{

    protected $areaOne;
    protected $customerOne;
    protected $customerTwo;
    protected $customerAssignmentOne;
    protected $customerAssignmentTwo;
    protected $customerAssignmentThree;
    protected $initialSalesActivity;
    protected $initialCustomerJourney;
    protected $customerJourneyOne;
    protected $salesActivityScheduleOneA;
    protected $salesActivityScheduleOneB;
    protected $salesActivityScheduleTwoA;

    protected $customerPayload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('CustomerJourney')->truncate();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();

        $this->initialCustomerJourney = new EntityRecord(CustomerJourney::class, 'initial');
        $this->initialCustomerJourney->columns['initial'] = true;
        $this->customerJourneyOne = new EntityRecord(CustomerJourney::class, 1);

        $this->areaOne = new EntityRecord(Area::class, 1);
        
        $this->customerOne = new EntityRecord(Customer::class, 1);
        $this->customerOne->columns['Area_id'] = $this->area->columns['id'];
        $this->customerTwo = new EntityRecord(Customer::class, 2);
        $this->customerTwo->columns['Area_id'] = $this->area->columns['id'];
        $this->customerThree = new EntityRecord(Customer::class, 3);
        $this->customerThree->columns['Area_id'] = $this->area->columns['id'];

        $this->initialSalesActivity = new EntityRecord(SalesActivity::class, 'initial');
        $this->initialSalesActivity->columns['initial'] = true;
        $this->initialSalesActivity->columns['duration'] = 30;

        $this->customerAssignmentOne = new EntityRecord(CustomerAssignment::class, 1);
        $this->customerAssignmentOne->columns['Sales_id'] = $this->sales->columns['id'];
        $this->customerAssignmentOne->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->customerAssignmentOne->columns['CustomerJourney_id'] = $this->initialCustomerJourney->columns['id'];
        $this->customerAssignmentOne->columns['status'] = CustomerAssignmentStatus::ACTIVE->value;
        $this->customerAssignmentTwo = new EntityRecord(CustomerAssignment::class, 2);
        $this->customerAssignmentTwo->columns['Sales_id'] = $this->sales->columns['id'];
        $this->customerAssignmentTwo->columns['Customer_id'] = $this->customerTwo->columns['id'];
        $this->customerAssignmentTwo->columns['CustomerJourney_id'] = $this->initialCustomerJourney->columns['id'];
        $this->customerAssignmentTwo->columns['status'] = CustomerAssignmentStatus::ACTIVE->value;
        $this->customerAssignmentThree = new EntityRecord(CustomerAssignment::class, 3);
        $this->customerAssignmentThree->columns['Sales_id'] = $this->sales->columns['id'];
        $this->customerAssignmentThree->columns['Customer_id'] = $this->customerThree->columns['id'];
        $this->customerAssignmentThree->columns['CustomerJourney_id'] = $this->initialCustomerJourney->columns['id'];
        $this->customerAssignmentThree->columns['status'] = CustomerAssignmentStatus::ACTIVE->value;
        
        $this->salesActivityScheduleOneA = new EntityRecord(SalesActivitySchedule::class, 'OneA');
        $this->salesActivityScheduleOneA->columns['CustomerAssignment_id'] = $this->customerAssignmentOne->columns['id'];
        $this->salesActivityScheduleOneA->columns['status'] = SalesActivityScheduleStatus::COMPLETED->value;
        $this->salesActivityScheduleOneB = new EntityRecord(SalesActivitySchedule::class, 'OneB');
        $this->salesActivityScheduleOneB->columns['CustomerAssignment_id'] = $this->customerAssignmentOne->columns['id'];
        $this->salesActivityScheduleOneB->columns['status'] = SalesActivityScheduleStatus::SCHEDULED->value;
        $this->salesActivityScheduleTwoA = new EntityRecord(SalesActivitySchedule::class, 'TwoA');
        $this->salesActivityScheduleTwoA->columns['CustomerAssignment_id'] = $this->customerAssignmentTwo->columns['id'];
        $this->salesActivityScheduleTwoA->columns['status'] = SalesActivityScheduleStatus::COMPLETED->value;
        
        $this->customerPayload = [
            'Area_id' => $this->areaOne->columns['id'],
            'name' => 'new customer name',
            'email' => 'newAddress@email.org',
        ];
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('CustomerJourney')->truncate();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
    }

    //
    protected function updateJourney()
    {
        $this->prepareSalesDependency();
        $this->initialCustomerJourney->insert($this->connection);
        $this->customerJourneyOne->insert($this->connection);

        $this->customerOne->insert($this->connection);

        $this->customerAssignmentOne->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID, $CustomerJourney_id: ID ) {
    updateCustomerAssignmentJourney ( id: $id, CustomerJourney_id: $CustomerJourney_id ) {
        id, customerJourney { id, name, initial }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->customerAssignmentOne->columns['id'],
            'CustomerJourney_id' => $this->customerJourneyOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_udpateJourney_200()
    {
        $this->updateJourney();
        $this->seeStatusCode(200);

        $this->seeJsonContains([
            'id' => $this->customerAssignmentOne->columns['id'],
            'customerJourney' => [
                'id' => $this->customerJourneyOne->columns['id'],
                'name' => $this->customerJourneyOne->columns['name'],
                'initial' => $this->customerJourneyOne->columns['initial'],
            ],
        ]);

        $this->seeInDatabase('CustomerAssignment',
                [
            'id' => $this->customerAssignmentOne->columns['id'],
            'CustomerJourney_id' => $this->customerJourneyOne->columns['id'],
        ]);
    }

    //
    protected function updateCustomerBio()
    {
        $this->prepareSalesDependency();
        $this->initialCustomerJourney->insert($this->connection);
        $this->customerJourneyOne->insert($this->connection);

        $this->areaOne->insert($this->connection);
        $this->customerOne->insert($this->connection);

        $this->customerAssignmentOne->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID, $customer: CustomerInput ) {
    updateCustomerBio ( id: $id, customer: $customer ) {
        id, customer { name, email, source, area { id } }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->customerAssignmentOne->columns['id'],
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
            'id' => $this->customerAssignmentOne->columns['id'],
            'customer' => [
                'name' => $this->customerPayload['name'],
                'email' => $this->customerPayload['email'],
                'source' => $this->customerOne->columns['source'],
                'area' => [
                    'id' => $this->customerPayload['Area_id'],
                ],
            ],
        ]);

        $this->seeInDatabase('Customer', [
            'id' => $this->customerAssignmentOne->columns['Customer_id'],
            'name' => $this->customerPayload['name'],
            'email' => $this->customerPayload['email'],
            'source' => $this->customerOne->columns['source'],
            'Area_id' => $this->customerPayload['Area_id'],
        ]);
    }

    //
    protected function viewDetail()
    {
        $this->prepareSalesDependency();
        $this->customerOne->insert($this->connection);

        $this->customerAssignmentOne->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
query ( $id: ID!) {
    customerAssignmentDetail ( id: $id ) {
        id, status, createdTime
        customer {
            id, name, email
            area { id, name }
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'salesId' => $this->sales->columns['id'],
            'id' => $this->customerAssignmentOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_viewDetail_200()
    {
        $this->viewDetail();
        $this->seeJsonContains([
            'id' => $this->customerAssignmentOne->columns['id'],
            'status' => $this->customerAssignmentOne->columns['status'],
            'createdTime' => $this->jakartaDateTimeFormat($this->customerAssignmentOne->columns['createdTime']),
            'customer' => [
                'id' => $this->customerOne->columns['id'],
                'name' => $this->customerOne->columns['name'],
                'email' => $this->customerOne->columns['email'],
                'area' => [
                    'id' => $this->area->columns['id'],
                    'name' => $this->area->columns['name'],
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
        
        $this->salesActivityScheduleOneA->insert($this->connection);
        $this->salesActivityScheduleOneB->insert($this->connection);
        $this->salesActivityScheduleTwoA->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
query ( $filters: [FilterInput] ) {
    customerAssignmentList ( filters: $filters ) {
        list {
            id, status, createdTime
            customer {
                id, name, email
                area { id, name }
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
                    'id' => $this->customerAssignmentOne->columns['id'],
                    'status' => $this->customerAssignmentOne->columns['status'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->customerAssignmentOne->columns['createdTime']),
                    'customer' => [
                        'id' => $this->customerOne->columns['id'],
                        'name' => $this->customerOne->columns['name'],
                        'email' => $this->customerOne->columns['email'],
                        'area' => [
                            'id' => $this->area->columns['id'],
                            'name' => $this->area->columns['name'],
                        ],
                    ],
                ],
                [
                    'id' => $this->customerAssignmentTwo->columns['id'],
                    'status' => $this->customerAssignmentTwo->columns['status'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->customerAssignmentTwo->columns['createdTime']),
                    'customer' => [
                        'id' => $this->customerTwo->columns['id'],
                        'name' => $this->customerTwo->columns['name'],
                        'email' => $this->customerTwo->columns['email'],
                        'area' => [
                            'id' => $this->area->columns['id'],
                            'name' => $this->area->columns['name'],
                        ],
                    ],
                ],
                [
                    'id' => $this->customerAssignmentThree->columns['id'],
                    'status' => $this->customerAssignmentThree->columns['status'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->customerAssignmentThree->columns['createdTime']),
                    'customer' => [
                        'id' => $this->customerThree->columns['id'],
                        'name' => $this->customerThree->columns['name'],
                        'email' => $this->customerThree->columns['email'],
                        'area' => [
                            'id' => $this->area->columns['id'],
                            'name' => $this->area->columns['name'],
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
        $this->customerAssignmentOne->columns['status'] = CustomerAssignmentStatus::GOOD_FUND->value;
        $this->filters = [
            ['column' => 'CustomerAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value]
        ];
        $this->viewList();
        $this->seeStatusCode(200);
        $this->seeJsonDoesntContains(['id' => $this->customerAssignmentOne->columns['id']]);
        $this->seeJsonContains(['id' => $this->customerAssignmentTwo->columns['id']]);
        $this->seeJsonContains(['id' => $this->customerAssignmentThree->columns['id']]);
        $this->seeJsonContains(['total' => 2]);
    }
    public function test_viewList_newAssignments_200()
    {
$this->disableExceptionHandling();
        $this->customerAssignmentOne->columns['status'] = CustomerAssignmentStatus::GOOD_FUND->value;
        $this->filters = [
            ['column' => 'CustomerAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
            ['column' => 'hasSalesActivitySchedule', 'value' => false]
        ];
        $this->viewList();
        $this->seeStatusCode(200);
        $this->seeJsonDoesntContains(['id' => $this->customerAssignmentOne->columns['id']]);
        $this->seeJsonDoesntContains(['id' => $this->customerAssignmentTwo->columns['id']]);
        $this->seeJsonContains(['id' => $this->customerAssignmentThree->columns['id']]);
        $this->seeJsonContains(['total' => 1]);
    }
    public function test_viewList_idleAssignments_200()
    {
$this->disableExceptionHandling();
        $this->customerAssignmentOne->columns['status'] = CustomerAssignmentStatus::GOOD_FUND->value;
        $this->filters = [
            ['column' => 'CustomerAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
            ['column' => 'hasSalesActivitySchedule', 'value' => true],
            ['column' => 'hasActiveSalesActivitySchedule', 'value' => false],
            ['column' => 'hasPendingRecycleRequest', 'value' => false],
            ['column' => 'hasPendingClosingRequest', 'value' => false],
        ];
        $this->viewList();
        $this->seeStatusCode(200);
        $this->seeJsonDoesntContains(['id' => $this->customerAssignmentOne->columns['id']]);
        $this->seeJsonContains(['id' => $this->customerAssignmentTwo->columns['id']]);
        $this->seeJsonDoesntContains(['id' => $this->customerAssignmentThree->columns['id']]);
        $this->seeJsonContains(['total' => 1]);
    }

    //
    protected function viewTotalCustomerAssignment()
    {
        $this->prepareSalesDependency();
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerThree->insert($this->connection);

        $this->customerAssignmentOne->insert($this->connection);
        $this->customerAssignmentTwo->insert($this->connection);
        $this->customerAssignmentThree->insert($this->connection);
        
        $this->salesActivityScheduleOneA->insert($this->connection);
        $this->salesActivityScheduleOneB->insert($this->connection);
        $this->salesActivityScheduleTwoA->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
query ( $filters: [FilterInput] ) {
    totalCustomerAssignment (filters: $filters)
}
_QUERY;
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_viewTotalCustomerAssignment_200()
    {
        $this->disableExceptionHandling();
        $this->viewTotalCustomerAssignment();
        $this->seeJsonContains(['totalCustomerAssignment' => 3]);
    }
    public function test_viewTotalCustomerAssignment_activeOnly_200()
    {
        $this->customerAssignmentThree->columns['status'] = CustomerAssignmentStatus::RECYCLED->value;
        $this->graphqlVariables['filters'] = [
            ['column' => 'CustomerAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
        ];
        $this->viewTotalCustomerAssignment();
        $this->seeJsonContains(['totalCustomerAssignment' => 2]);
    }
    public function test_viewTotalCustomerAssignment_recycleOnly_200()
    {
        $this->customerAssignmentThree->columns['status'] = CustomerAssignmentStatus::RECYCLED->value;
        $this->graphqlVariables['filters'] = [
            ['column' => 'CustomerAssignment.status', 'value' => CustomerAssignmentStatus::RECYCLED->value],
        ];
        $this->viewTotalCustomerAssignment();
        $this->seeJsonContains(['totalCustomerAssignment' => 1]);
    }
    public function test_viewTotalCustomerAssignment_goodFund_200()
    {
        $this->customerAssignmentOne->columns['status'] = CustomerAssignmentStatus::GOOD_FUND->value;
        $this->customerAssignmentTwo->columns['status'] = CustomerAssignmentStatus::GOOD_FUND->value;
        $this->graphqlVariables['filters'] = [
            ['column' => 'CustomerAssignment.status', 'value' => CustomerAssignmentStatus::GOOD_FUND->value],
        ];
        $this->viewTotalCustomerAssignment();
        $this->seeJsonContains(['totalCustomerAssignment' => 2]);
    }
    public function test_viewTotalCustomerAssignment_newAssignment_200()
    {
        $this->graphqlVariables['filters'] = [
            ['column' => 'CustomerAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
            ['column' => 'hasSalesActivitySchedule', 'value' => false],
        ];
        $this->viewTotalCustomerAssignment();
        $this->seeJsonContains(['totalCustomerAssignment' => 1]);
    }
    public function test_viewTotalCustomerAssignment_idleAssignment_200()
    {
        $this->graphqlVariables['filters'] = [
            ['column' => 'CustomerAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
            ['column' => 'hasSalesActivitySchedule', 'value' => true],
            ['column' => 'hasActiveSalesActivitySchedule', 'value' => false],
            ['column' => 'hasPendingClosingRequest', 'value' => false],
            ['column' => 'hasPendingRecycleRequest', 'value' => false],
        ];
        $this->viewTotalCustomerAssignment();
        $this->seeJsonContains(['totalCustomerAssignment' => 1]);
    }

    //
    public function test_multiRequest_200()
    {
        $this->prepareSalesDependency();
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerThree->insert($this->connection);

        $this->customerAssignmentThree->columns['status'] = CustomerAssignmentStatus::GOOD_FUND->value;
        $this->customerAssignmentOne->insert($this->connection);
        $this->customerAssignmentTwo->insert($this->connection);
        $this->customerAssignmentThree->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
query (
    $activeAssignmentFilters: [FilterInput],
    $recycleAssignmentFilters: [FilterInput],
    $goodFundAssignmentFilters: [FilterInput]
) {
    totalCustomerAssignment,
    totalActiveCustomerAssignment: totalCustomerAssignment ( filters: $activeAssignmentFilters ),
    totalRecycleCustomerAssignment: totalCustomerAssignment ( filters: $recycleAssignmentFilters ),
    totalGoodFundCustomerAssignment: totalCustomerAssignment ( filters: $goodFundAssignmentFilters ),
}
_QUERY;
        $this->graphqlVariables = [
            'activeAssignmentFilters' => [
                ['column' => 'CustomerAssignment.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
            ],
            'recycleAssignmentFilters' => [
                ['column' => 'CustomerAssignment.status', 'value' => CustomerAssignmentStatus::RECYCLED->value],
            ],
            'goodFundAssignmentFilters' => [
                ['column' => 'CustomerAssignment.status', 'value' => CustomerAssignmentStatus::GOOD_FUND->value],
            ],
        ];
        $this->postGraphqlRequest($this->sales->token);

        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'totalCustomerAssignment' => 3,
            'totalActiveCustomerAssignment' => 2,
            'totalRecycleCustomerAssignment' => 0,
            'totalGoodFundCustomerAssignment' => 1,
        ]);
    }
}
