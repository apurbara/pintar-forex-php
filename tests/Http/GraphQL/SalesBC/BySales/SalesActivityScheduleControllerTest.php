<?php

namespace App\Http\Controllers\SalesBC\BySales;

use Company\Domain\Model\SalesActivity;
use DateTime;
use DateTimeImmutable;
use Sales\Domain\DependencyModel\AreaStructure\Area\Customer;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use SharedContext\Domain\Enum\SalesActivityScheduleStatus;
use Tests\Http\GraphQL\SalesBC\SalesBCTestCase;
use Tests\Http\Record\EntityRecord;

class SalesActivityScheduleControllerTest extends SalesBCTestCase
{
    protected $salesActivity;
    protected $initialSalesActivity;
    protected $salesActivityOne;
    
    protected $customer;
    protected $customerOne;
    protected $customerTwo;
    protected $customerThree;
    
    protected $customerAssignment;
    protected $customerAssignmentOne;
    protected $customerAssignmentTwo;
    protected $customerAssignmentThree;
    
    protected $salesActivityScheduleOne;
    protected $salesActivityScheduleTwo;
    protected $salesActivityScheduleThree;
    
    protected $submitScheduleRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
        
        $this->salesActivity = new EntityRecord(SalesActivity::class, 'main');
        $this->salesActivity->columns['duration'] = 20;
        $this->salesActivityOne = new EntityRecord(SalesActivity::class, '1');
        $this->salesActivityOne->columns['duration'] = 30;
        $this->initialSalesActivity = new EntityRecord(SalesActivity::class, 'initial');
        $this->initialSalesActivity->columns['duration'] = 15;
        $this->initialSalesActivity->columns['initial'] = true;
        
        $this->customer = new EntityRecord(Customer::class, 'main');
        $this->customerOne = new EntityRecord(Customer::class, 1);
        $this->customerTwo = new EntityRecord(Customer::class, 2);
        $this->customerThree = new EntityRecord(Customer::class, 3);
        
        $this->customerAssignment = new EntityRecord(CustomerAssignment::class, 'main');
        $this->customerAssignment->columns['Customer_id'] = $this->customer->columns['id'];
        $this->customerAssignment->columns['Sales_id'] = $this->sales->columns['id'];
        $this->customerAssignmentOne = new EntityRecord(CustomerAssignment::class, 1);
        $this->customerAssignmentOne->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->customerAssignmentOne->columns['Sales_id'] = $this->sales->columns['id'];
        $this->customerAssignmentTwo = new EntityRecord(CustomerAssignment::class, 2);
        $this->customerAssignmentTwo->columns['Customer_id'] = $this->customerTwo->columns['id'];
        $this->customerAssignmentTwo->columns['Sales_id'] = $this->sales->columns['id'];
        $this->customerAssignmentThree = new EntityRecord(CustomerAssignment::class, 3);
        $this->customerAssignmentThree->columns['Customer_id'] = $this->customerThree->columns['id'];
        $this->customerAssignmentThree->columns['Sales_id'] = $this->sales->columns['id'];
        
        $this->salesActivityScheduleOne = new EntityRecord(SalesActivitySchedule::class, 1);
        $this->salesActivityScheduleOne->columns['SalesActivity_id'] = $this->salesActivityOne->columns['id'];
        $this->salesActivityScheduleOne->columns['CustomerAssignment_id'] = $this->customerAssignmentOne->columns['id'];
        $this->salesActivityScheduleOne->columns['startTime'] = (new \DateTime('next monday'))->setTime(10, 0)->format('Y-m-d H') . ":00:00";
        $this->salesActivityScheduleOne->columns['endTime'] = (new \DateTime('next monday'))->setTime(11, 0)->format('Y-m-d H') . ":00:00";
//        $this->salesActivityScheduleOne->columns['endTime'] = (new \DateTime('+49 hours'))->format('Y-m-d H') . ":00:00";
        $this->salesActivityScheduleTwo = new EntityRecord(SalesActivitySchedule::class, 2);
        $this->salesActivityScheduleTwo->columns['SalesActivity_id'] = $this->initialSalesActivity->columns['id'];
        $this->salesActivityScheduleTwo->columns['CustomerAssignment_id'] = $this->customerAssignmentTwo->columns['id'];
        $this->salesActivityScheduleTwo->columns['startTime'] = (new \DateTime('next monday'))->setTime(10, 0)->format('Y-m-d H') . ":00:00";
        $this->salesActivityScheduleTwo->columns['endTime'] = (new \DateTime('next monday'))->setTime(11, 0)->format('Y-m-d H') . ":00:00";
        $this->salesActivityScheduleThree = new EntityRecord(SalesActivitySchedule::class, 3);
        $this->salesActivityScheduleThree->columns['SalesActivity_id'] = $this->salesActivity->columns['id'];
        $this->salesActivityScheduleThree->columns['CustomerAssignment_id'] = $this->customerAssignmentThree->columns['id'];
        $this->salesActivityScheduleThree->columns['startTime'] = (new \DateTime('next monday'))->setTime(11, 0)->format('Y-m-d H') . ":00:00";
        $this->salesActivityScheduleThree->columns['endTime'] = (new \DateTime('next monday'))->setTime(12, 0)->format('Y-m-d H') . ":00:00";
        
        $this->submitScheduleRequest = [
            'SalesActivity_id' => $this->salesActivity->columns['id'],
            'startTime' => (new DateTimeImmutable('next monday'))->setTime(10, 0)->format('Y-m-d H:i:s'),
        ];
    }
    protected function tearDown(): void
    {
//        parent::tearDown();
//        $this->connection->table('SalesActivity')->truncate();
//        $this->connection->table('Customer')->truncate();
//        $this->connection->table('CustomerAssignment')->truncate();
//        $this->connection->table('SalesActivitySchedule')->truncate();
    }
    
    //
    protected function submitSchedule()
    {
        $this->prepareSalesDependency();
        $this->salesActivity->insert($this->connection);
        $this->customerAssignment->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $CustomerAssignment_id: ID!, $SalesActivity_id: ID!, $startTime: DateTimeZ ) {
    submitSalesActivitySchedule ( CustomerAssignment_id: $CustomerAssignment_id, SalesActivity_id: $SalesActivity_id, startTime: $startTime ) {
        id, status, startTime
        salesActivity { id, name, duration }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'CustomerAssignment_id' => $this->customerAssignment->columns['id'],
            ...$this->submitScheduleRequest
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_submitSchedule_200()
    {
        $this->submitSchedule();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'status' => 'SCHEDULED',
            'startTime' => $this->jakartaDateTimeFormat((new DateTime($this->submitScheduleRequest['startTime']))->format('Y-m-d H') . ":00:00"),
            'salesActivity' => [
                'id' => $this->submitScheduleRequest['SalesActivity_id'],
                'name' => $this->salesActivity->columns['name'],
                'duration' => $this->salesActivity->columns['duration'],
            ],
        ]);
        
        $this->seeInDatabase('SalesActivitySchedule', [
            'SalesActivity_id' => $this->salesActivity->columns['id'],
            'CustomerAssignment_id' => $this->customerAssignment->columns['id'],
            'status' => 'SCHEDULED',
        ]);
    }
    public function test_submitSchedule_relocateConflictedInitialScheduler()
    {
        $this->initialSalesActivity->insert($this->connection);
        $this->salesActivityOne->insert($this->connection);
        
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerThree->insert($this->connection);
        
        $this->customerAssignmentOne->insert($this->connection);
        $this->customerAssignmentTwo->insert($this->connection);
        $this->customerAssignmentThree->insert($this->connection);
        
        $this->salesActivityScheduleOne->insert($this->connection);
        $this->salesActivityScheduleTwo->insert($this->connection);
        $this->salesActivityScheduleThree->insert($this->connection);
        
        $this->submitSchedule();
        $this->seeStatusCode(200);
        
        $this->seeInDatabase('SalesActivitySchedule', [
            'id' => $this->salesActivityScheduleTwo->columns['id'],
            'startTime' => (new DateTimeImmutable('next monday'))->setTime(11, 0)->format('Y-m-d H:i:s'),
        ]);
    }
    
    //
    protected function viewList()
    {
        $this->prepareSalesDependency();
        
        $this->salesActivity->insert($this->connection);
        $this->salesActivityOne->insert($this->connection);
        $this->initialSalesActivity->insert($this->connection);
        
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        
        $this->customerAssignmentOne->insert($this->connection);
        $this->customerAssignmentTwo->insert($this->connection);
        
        $this->salesActivityScheduleOne->insert($this->connection);
        $this->salesActivityScheduleTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query {
    salesActivityScheduleList {
        list {
            id, status, startTime, endTime
            customerAssignment {
                id, 
                customer { id, name }
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
                    'id' => $this->salesActivityScheduleOne->columns['id'],
                    'status' => $this->salesActivityScheduleOne->columns['status'],
                    'startTime' => $this->jakartaDateTimeFormat($this->salesActivityScheduleOne->columns['startTime']),
                    'endTime' => $this->jakartaDateTimeFormat($this->salesActivityScheduleOne->columns['endTime']),
                    'customerAssignment' => [
                        'id' => $this->customerAssignmentOne->columns['id'],
                        'customer' => [
                            'id' => $this->customerOne->columns['id'],
                            'name' => $this->customerOne->columns['name'],
                        ],
                    ],
                ],
                [
                    'id' => $this->salesActivityScheduleTwo->columns['id'],
                    'status' => $this->salesActivityScheduleTwo->columns['status'],
                    'startTime' => $this->jakartaDateTimeFormat($this->salesActivityScheduleTwo->columns['startTime']),
                    'endTime' => $this->jakartaDateTimeFormat($this->salesActivityScheduleTwo->columns['endTime']),
                    'customerAssignment' => [
                        'id' => $this->customerAssignmentTwo->columns['id'],
                        'customer' => [
                            'id' => $this->customerTwo->columns['id'],
                            'name' => $this->customerTwo->columns['name'],
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
    protected function viewSummaryList()
    {
        $this->prepareSalesDependency();
        
        $this->salesActivity->insert($this->connection);
        
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        
        $this->customerAssignmentOne->insert($this->connection);
        $this->customerAssignmentTwo->insert($this->connection);
        
        $this->salesActivityScheduleOne->insert($this->connection);
        $this->salesActivityScheduleTwo->columns['status'] = SalesActivityScheduleStatus::COMPLETED->value;
        $this->salesActivityScheduleTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query {
    salesActivityScheduleSummaryList {
        total, startTime, endTime, status
    }
}
_QUERY;
        $this->graphqlVariables = $this->getPaginationInput();
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_viewSummaryList_200()
    {
        $this->viewSummaryList();
        $this->seeJsonContains([
                'total' => 1,
                'startTime' => $this->jakartaDateTimeFormat($this->salesActivityScheduleOne->columns['startTime']),
                'endTime' => $this->jakartaDateTimeFormat($this->salesActivityScheduleOne->columns['endTime']),
                'status' => $this->salesActivityScheduleOne->columns['status'],
        ]);
        $this->seeJsonContains([
                'total' => 1,
                'startTime' => $this->jakartaDateTimeFormat($this->salesActivityScheduleTwo->columns['startTime']),
                'endTime' => $this->jakartaDateTimeFormat($this->salesActivityScheduleTwo->columns['endTime']),
                'status' => $this->salesActivityScheduleTwo->columns['status'],
        ]);
//        $this->printApiSpesification();
    }
    
    //
    protected function viewDetail()
    {
        $this->prepareSalesDependency();
        
        $this->salesActivity->insert($this->connection);
        
        $this->customerOne->insert($this->connection);
        
        $this->customerAssignmentOne->insert($this->connection);
        
        $this->salesActivityScheduleOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $id: ID!) {
    salesActivityScheduleDetail ( id: $id ) {
        id, status, startTime, endTime
        customerAssignment {
            id, 
            customer { id, name }
        }
    }
}
_QUERY;
        $this->graphqlVariables['salesId'] = $this->sales->columns['id'];
        $this->graphqlVariables['id'] = $this->salesActivityScheduleOne->columns['id'];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_viewDetail_200()
    {
        $this->viewDetail();
        $this->seeJsonContains([
            'id' => $this->salesActivityScheduleOne->columns['id'],
            'status' => $this->salesActivityScheduleOne->columns['status'],
            'startTime' => $this->jakartaDateTimeFormat($this->salesActivityScheduleOne->columns['startTime']),
            'endTime' => $this->jakartaDateTimeFormat($this->salesActivityScheduleOne->columns['endTime']),
            'customerAssignment' => [
                'id' => $this->customerAssignmentOne->columns['id'],
                'customer' => [
                    'id' => $this->customerOne->columns['id'],
                    'name' => $this->customerOne->columns['name'],
                ],
            ],
        ]);
    }
    
    //
    protected function viewTotal()
    {
        $this->prepareSalesDependency();
        
        $this->salesActivity->insert($this->connection);
        $this->salesActivityOne->insert($this->connection);
        $this->initialSalesActivity->insert($this->connection);
        
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerThree->insert($this->connection);
        
        $this->customerAssignmentOne->insert($this->connection);
        $this->customerAssignmentTwo->insert($this->connection);
        $this->customerAssignmentThree->insert($this->connection);
        
        $this->salesActivityScheduleOne->columns['startTime'] = (new DateTimeImmutable('-2 days'))->setTime(10, 0)->format('Y-m-d H:i:s');
        $this->salesActivityScheduleOne->columns['endTime'] = (new DateTimeImmutable('-2 days'))->setTime(11, 0)->format('Y-m-d H:i:s');
        $this->salesActivityScheduleOne->insert($this->connection);
        $this->salesActivityScheduleTwo->insert($this->connection);
        $this->salesActivityScheduleThree->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $totalUpcomingFilters: [FilterInput], $totalPastScheduleWithoutReportFilters: [FilterInput]) {
    totalUpcomingSchedule: totalSalesActivitySchedule ( filters: $totalUpcomingFilters),
    totalPastScheduleWithoutReport: totalSalesActivitySchedule ( filters: $totalPastScheduleWithoutReportFilters)
}
_QUERY;
        $this->graphqlVariables = [
            'totalPastScheduleWithoutReportFilters' => [
                ['column' => 'SalesActivitySchedule.endTime', 'value' => (new \DateTime())->format('Y-m-d H') . ":00:00", 'comparisonType' => 'LTE'],
                ['column' => 'SalesActivitySchedule.status', 'value' => SalesActivityScheduleStatus::SCHEDULED->value],
            ],
            'totalUpcomingFilters' => [
                ['column' => 'SalesActivitySchedule.startTime', 'value' => (new \DateTime())->format('Y-m-d H') . ":00:00", 'comparisonType' => 'GTE'],
                ['column' => 'SalesActivitySchedule.status', 'value' => SalesActivityScheduleStatus::SCHEDULED->value],
            ],
            
        ]; 
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_viewTotal_200()
    {
        $this->viewTotal();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'totalUpcomingSchedule' => 2,
            'totalPastScheduleWithoutReport' => 1,
        ]);
    }
    
    //
    protected function viewAllNonInitialSchedulesInMonth()
    {
        $this->prepareSalesDependency();
        
        $this->customer->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerThree->insert($this->connection);
        
        $this->customerAssignment->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->customerAssignmentTwo->insert($this->connection);
        $this->customerAssignmentThree->insert($this->connection);
        
        $this->salesActivity->insert($this->connection);
        $this->salesActivityOne->insert($this->connection);
        $this->initialSalesActivity->insert($this->connection);
        
        $this->salesActivityScheduleOne->columns['startTime'] = (new DateTime('-2 months'))->format('Y-m-d H:i:s');
        $this->salesActivityScheduleThree->columns['startTime'] = (new DateTime())->format('Y-m-d H:i:s');
        
        $this->salesActivityScheduleOne->insert($this->connection);
        $this->salesActivityScheduleTwo->insert($this->connection);
        $this->salesActivityScheduleThree->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $year: Int, $month: Int ) {
    viewAllNonInitialSchedulesInMonth ( year: $year, month: $month ) {
        id,
        salesActivity { name }
        customerAssignment { customer { name } }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'year' => (int) (new DateTime())->format('Y'),
            'month' => (int) (new DateTime())->format('m'),
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_viewAllNonInitialSchedulesInMonth_200()
    {
        $this->viewAllNonInitialSchedulesInMonth();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'id' => $this->salesActivityScheduleThree->columns['id'],
            'salesActivity' => [
                'name' => $this->salesActivity->columns['name']
            ],
            'customerAssignment' => [
                'customer' => [
                    'name' => $this->customerThree->columns['name'],
                ]
            ],
        ]);
    }
    
    //
    protected function viewAllNonInitialSchedules()
    {
        $this->prepareSalesDependency();
        
        $this->customer->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerThree->insert($this->connection);
        
        $this->customerAssignment->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->customerAssignmentTwo->insert($this->connection);
        $this->customerAssignmentThree->insert($this->connection);
        
        $this->salesActivity->insert($this->connection);
        $this->salesActivityOne->insert($this->connection);
        $this->initialSalesActivity->insert($this->connection);
        
        $this->salesActivityScheduleOne->columns['startTime'] = (new DateTime('-2 months'))->format('Y-m-d H:i:s');
        $this->salesActivityScheduleThree->columns['startTime'] = (new DateTime())->format('Y-m-d H:i:s');
        
        $this->salesActivityScheduleOne->insert($this->connection);
        $this->salesActivityScheduleTwo->insert($this->connection);
        $this->salesActivityScheduleThree->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query {
    viewAllNonInitialSchedules {
        id,
        salesActivity { name }
        customerAssignment { customer { name } }
    }
}
_QUERY;
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_viewAllNonInitialSchedules_200()
    {
        $this->viewAllNonInitialSchedules();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'id' => $this->salesActivityScheduleThree->columns['id'],
            'salesActivity' => [
                'name' => $this->salesActivity->columns['name']
            ],
            'customerAssignment' => [
                'customer' => [
                    'name' => $this->customerThree->columns['name'],
                ]
            ],
        ]);
        $this->seeJsonContains([
            'id' => $this->salesActivityScheduleOne->columns['id'],
            'salesActivity' => [
                'name' => $this->salesActivityOne->columns['name']
            ],
            'customerAssignment' => [
                'customer' => [
                    'name' => $this->customerOne->columns['name'],
                ]
            ],
        ]);
    }
    
}
