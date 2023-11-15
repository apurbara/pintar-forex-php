<?php

namespace Tests\Http\GraphQL\SalesBC;

use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\SalesActivity;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use Tests\Http\Record\EntityRecord;

class AssignedCustomerControllerTest extends SalesBCTestCase
{

    protected $customerOne;
    protected $customerTwo;
    protected $assignedCustomerOne;
    protected $assignedCustomerTwo;
    protected $assignedCustomerThree;
    protected $initialSalesActivity;
    protected $initialCustomerJourney;
    protected $customerJourneyOne;
    protected $registerNewCustomerRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('AssignedCustomer')->truncate();
        $this->connection->table('CustomerJourney')->truncate();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();

        $this->area = new EntityRecord(Area::class, 'main');

        $this->initialCustomerJourney = new EntityRecord(CustomerJourney::class, 'initial');
        $this->initialCustomerJourney->columns['initial'] = true;
        $this->customerJourneyOne = new EntityRecord(CustomerJourney::class, 1);

        $this->customerOne = new EntityRecord(Customer::class, 1);
        $this->customerOne->columns['Area_id'] = $this->area->columns['id'];
        $this->customerTwo = new EntityRecord(Customer::class, 2);
        $this->customerTwo->columns['Area_id'] = $this->area->columns['id'];
        $this->customerThree = new EntityRecord(Customer::class, 3);
        $this->customerThree->columns['Area_id'] = $this->area->columns['id'];

        $this->initialSalesActivity = new EntityRecord(SalesActivity::class, 'initial');
        $this->initialSalesActivity->columns['initial'] = true;
        $this->initialSalesActivity->columns['duration'] = 30;

        $this->assignedCustomerOne = new EntityRecord(AssignedCustomer::class, 1);
        $this->assignedCustomerOne->columns['Sales_id'] = $this->sales->columns['id'];
        $this->assignedCustomerOne->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->assignedCustomerOne->columns['CustomerJourney_id'] = $this->initialCustomerJourney->columns['id'];
        $this->assignedCustomerOne->columns['status'] = CustomerAssignmentStatus::ACTIVE->value;
        $this->assignedCustomerTwo = new EntityRecord(AssignedCustomer::class, 2);
        $this->assignedCustomerTwo->columns['Sales_id'] = $this->sales->columns['id'];
        $this->assignedCustomerTwo->columns['Customer_id'] = $this->customerTwo->columns['id'];
        $this->assignedCustomerTwo->columns['CustomerJourney_id'] = $this->initialCustomerJourney->columns['id'];
        $this->assignedCustomerTwo->columns['status'] = CustomerAssignmentStatus::RECYCLED->value;
        $this->assignedCustomerThree = new EntityRecord(AssignedCustomer::class, 3);
        $this->assignedCustomerThree->columns['Sales_id'] = $this->sales->columns['id'];
        $this->assignedCustomerThree->columns['Customer_id'] = $this->customerThree->columns['id'];
        $this->assignedCustomerThree->columns['CustomerJourney_id'] = $this->initialCustomerJourney->columns['id'];
        $this->assignedCustomerThree->columns['status'] = CustomerAssignmentStatus::GOOD_FUND->value;

        $this->registerNewCustomerRequest = [
            'areaId' => $this->area->columns['id'],
            'name' => 'new customer name',
            'email' => 'newCustomer@email.org',
            'phone' => '0813213123123',
        ];
    }

    protected function tearDown(): void
    {
//        parent::tearDown();
//        $this->connection->table('Customer')->truncate();
//        $this->connection->table('AssignedCustomer')->truncate();
//        $this->connection->table('CustomerJourney')->truncate();
//        $this->connection->table('SalesActivity')->truncate();
//        $this->connection->table('SalesActivitySchedule')->truncate();
    }

    //
    protected function registerNewCustomer()
    {
        $this->prepareSalesDependency();
        $this->initialCustomerJourney->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
mutation ( $salesId: ID!, $areaId: ID!, $name: String, $email: String, $phone: String ) {
    sales ( salesId: $salesId ) {
        registerNewCustomer ( areaId: $areaId, name: $name, email: $email, phone: $phone ) {
            id, status, createdTime
            customer {
                name, email, phone
                area { id, name }
            }
            customerJourney { id, name, initial }
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'salesId' => $this->sales->columns['id'],
            ...$this->registerNewCustomerRequest
        ];
        $this->postGraphqlRequest($this->personnel->token);
    }

    public function test_registerNewCustomer_200()
    {
        $this->registerNewCustomer();
        $this->seeStatusCode(200);

        $this->seeJsonContains([
            'status' => CustomerAssignmentStatus::ACTIVE->value,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'customer' => [
                'name' => $this->registerNewCustomerRequest['name'],
                'email' => $this->registerNewCustomerRequest['email'],
                'phone' => $this->registerNewCustomerRequest['phone'],
                'area' => [
                    'id' => $this->registerNewCustomerRequest['areaId'],
                    'name' => $this->area->columns['name'],
                ],
            ],
            'customerJourney' => [
                'id' => $this->initialCustomerJourney->columns['id'],
                'name' => $this->initialCustomerJourney->columns['name'],
                'initial' => true,
            ],
        ]);

        $this->seeInDatabase('AssignedCustomer',
                [
            'Sales_id' => $this->sales->columns['id'],
            'CustomerJourney_id' => $this->initialCustomerJourney->columns['id'],
            'status' => CustomerAssignmentStatus::ACTIVE->value,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
        ]);

        $this->seeInDatabase('Customer',
                [
            'Area_id' => $this->area->columns['id'],
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'name' => $this->registerNewCustomerRequest['name'],
            'email' => $this->registerNewCustomerRequest['email'],
            'phone' => $this->registerNewCustomerRequest['phone'],
        ]);
    }

    public function test_registerNewCustomer_200_allocateNewInitialSchedule()
    {
        $this->initialSalesActivity->insert($this->connection);
        $this->registerNewCustomer();
        
        if ((new \DateTimeImmutable())->format('w') == 5) {
            $this->seeInDatabase('SalesActivitySchedule', [
                'startTime' => (new \DateTimeImmutable('+3 Days'))->format('Y-m-d') . " 10:00:00",
                'SalesActivity_id' => $this->initialSalesActivity->columns['id'],
            ]);
        } elseif ((new \DateTimeImmutable())->format('w') == 6) {
            $this->seeInDatabase('SalesActivitySchedule', [
                'startTime' => (new \DateTimeImmutable('+2 Days'))->format('Y-m-d') . " 10:00:00",
                'SalesActivity_id' => $this->initialSalesActivity->columns['id'],
            ]);
        } else {
            $this->seeInDatabase('SalesActivitySchedule', [
                'startTime' => (new \DateTimeImmutable('+1 Days'))->format('Y-m-d') . " 10:00:00",
                'SalesActivity_id' => $this->initialSalesActivity->columns['id'],
            ]);
        }
    }

    //
    protected function updateJourney()
    {
        $this->prepareSalesDependency();
        $this->initialCustomerJourney->insert($this->connection);
        $this->customerJourneyOne->insert($this->connection);

        $this->customerOne->insert($this->connection);

        $this->assignedCustomerOne->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
mutation ( $salesId: ID!, $id: ID, $customerJourneyId: ID ) {
    sales ( salesId: $salesId ) {
        updateJourney ( id: $id, customerJourneyId: $customerJourneyId ) {
            id, customerJourney { id, name, initial }
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'salesId' => $this->sales->columns['id'],
            'id' => $this->assignedCustomerOne->columns['id'],
            'customerJourneyId' => $this->customerJourneyOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->personnel->token);
    }

    public function test_udpateJourney_200()
    {
        $this->updateJourney();
        $this->seeStatusCode(200);

        $this->seeJsonContains([
            'id' => $this->assignedCustomerOne->columns['id'],
            'customerJourney' => [
                'id' => $this->customerJourneyOne->columns['id'],
                'name' => $this->customerJourneyOne->columns['name'],
                'initial' => $this->customerJourneyOne->columns['initial'],
            ],
        ]);

        $this->seeInDatabase('AssignedCustomer',
                [
            'id' => $this->assignedCustomerOne->columns['id'],
            'CustomerJourney_id' => $this->customerJourneyOne->columns['id'],
        ]);
    }

    //
    protected function viewDetail()
    {
        $this->prepareSalesDependency();
        $this->customerOne->insert($this->connection);

        $this->assignedCustomerOne->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
query ( $salesId: ID!, $assignedCustomerId: ID!) {
    sales ( salesId: $salesId ) {
        assignedCustomerDetail ( assignedCustomerId: $assignedCustomerId ) {
            id, status, createdTime
            customer {
                id, name, email
                area { id, name }
            }
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'salesId' => $this->sales->columns['id'],
            'assignedCustomerId' => $this->assignedCustomerOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->personnel->token);
    }

    public function test_viewDetail_200()
    {
        $this->viewDetail();
        $this->seeJsonContains([
            'id' => $this->assignedCustomerOne->columns['id'],
            'status' => $this->assignedCustomerOne->columns['status'],
            'createdTime' => $this->jakartaDateTimeFormat($this->assignedCustomerOne->columns['createdTime']),
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

        $this->assignedCustomerOne->insert($this->connection);
        $this->assignedCustomerTwo->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
query ( $salesId: ID!) {
    sales ( salesId: $salesId ) {
        assignedCustomerList {
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
}
_QUERY;
        $this->graphqlVariables['salesId'] = $this->sales->columns['id'];
        $this->postGraphqlRequest($this->personnel->token);
    }

    public function test_viewList_200()
    {
        $this->viewList();
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->assignedCustomerOne->columns['id'],
                    'status' => $this->assignedCustomerOne->columns['status'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->assignedCustomerOne->columns['createdTime']),
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
                    'id' => $this->assignedCustomerTwo->columns['id'],
                    'status' => $this->assignedCustomerTwo->columns['status'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->assignedCustomerTwo->columns['createdTime']),
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
            ],
            'cursorLimit' => [
                'total' => 2,
                'cursorToNextPage' => null,
            ],
        ]);
    }

    //
    protected function viewTotalCustomerAssignment()
    {
        $this->prepareSalesDependency();
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerThree->insert($this->connection);

        $this->assignedCustomerOne->insert($this->connection);
        $this->assignedCustomerTwo->insert($this->connection);
        $this->assignedCustomerThree->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
query ( $salesId: ID!, $filters: [FilterInput] ) {
    sales ( salesId: $salesId ) {
        totalCustomerAssignment (filters: $filters)
    }
}
_QUERY;
        $this->graphqlVariables['salesId'] = $this->sales->columns['id'];
        $this->postGraphqlRequest($this->personnel->token);
    }

    public function test_viewTotalCustomerAssignment_200()
    {
        $this->disableExceptionHandling();
        $this->viewTotalCustomerAssignment();
        $this->seeJsonContains(['totalCustomerAssignment' => 3]);
    }

    public function test_viewTotalCustomerAssignment_activeOnly_200()
    {
        $this->assignedCustomerThree->columns['status'] = CustomerAssignmentStatus::ACTIVE->value;
        $this->graphqlVariables['filters'] = [
            ['column' => 'AssignedCustomer.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
        ];
        $this->viewTotalCustomerAssignment();
        $this->seeJsonContains(['totalCustomerAssignment' => 2]);
    }

    public function test_viewTotalCustomerAssignment_recycleOnly_200()
    {
        $this->graphqlVariables['filters'] = [
            ['column' => 'AssignedCustomer.status', 'value' => CustomerAssignmentStatus::RECYCLED->value],
        ];
        $this->viewTotalCustomerAssignment();
        $this->seeJsonContains(['totalCustomerAssignment' => 1]);
    }

    public function test_viewTotalCustomerAssignment_goodFund_200()
    {
        $this->assignedCustomerOne->columns['status'] = CustomerAssignmentStatus::GOOD_FUND->value;
        $this->graphqlVariables['filters'] = [
            ['column' => 'AssignedCustomer.status', 'value' => CustomerAssignmentStatus::GOOD_FUND->value],
        ];
        $this->viewTotalCustomerAssignment();
        $this->seeJsonContains(['totalCustomerAssignment' => 2]);
    }

    //
    public function test_multiRequest_200()
    {
        $this->prepareSalesDependency();
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerThree->insert($this->connection);

        $this->assignedCustomerThree->columns['status'] = CustomerAssignmentStatus::ACTIVE->value;
        $this->assignedCustomerOne->insert($this->connection);
        $this->assignedCustomerTwo->insert($this->connection);
        $this->assignedCustomerThree->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
query (
    $salesId: ID!, 
    $activeAssignmentFilters: [FilterInput],
    $recycleAssignmentFilters: [FilterInput],
    $goodFundAssignmentFilters: [FilterInput]
) {
    sales ( salesId: $salesId ) {
        totalCustomerAssignment,
        totalActiveCustomerAssignment: totalCustomerAssignment ( filters: $activeAssignmentFilters ),
        totalRecycleCustomerAssignment: totalCustomerAssignment ( filters: $recycleAssignmentFilters ),
        totalGoodFundCustomerAssignment: totalCustomerAssignment ( filters: $goodFundAssignmentFilters ),
    }
}
_QUERY;
        $this->graphqlVariables = [
            'salesId' => $this->sales->columns['id'],
            'activeAssignmentFilters' => [
                ['column' => 'AssignedCustomer.status', 'value' => CustomerAssignmentStatus::ACTIVE->value],
            ],
            'recycleAssignmentFilters' => [
                ['column' => 'AssignedCustomer.status', 'value' => CustomerAssignmentStatus::RECYCLED->value],
            ],
            'goodFundAssignmentFilters' => [
                ['column' => 'AssignedCustomer.status', 'value' => CustomerAssignmentStatus::GOOD_FUND->value],
            ],
        ];
        $this->postGraphqlRequest($this->personnel->token);

        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'totalCustomerAssignment' => 3,
            'totalActiveCustomerAssignment' => 2,
            'totalRecycleCustomerAssignment' => 1,
            'totalGoodFundCustomerAssignment' => 0,
        ]);
    }
}
