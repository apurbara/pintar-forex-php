<?php

namespace Sales\Application\Controllers;

use Company\Domain\Model\Customer;
use Company\Domain\Model\SalesActivity;
use DateTime;
use DateTimeImmutable;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\FactFindingAssignment;
use Sales\Domain\Model\Sales\GreetingAssignment;
use Sales\Domain\Model\Sales\StrikingAssignment;
use Shared\Domain\Enum\SalesActivityScheduleStatus;
use Shared\Domain\Enum\SalesRole;
use Tests\Http\Record\EntityRecord;
use Tests\Sales\Application\Controllers\SalesControllerTestCase;

class SalesActivityScheduleControllerTest extends SalesControllerTestCase
{
    protected $salesActivity;
    protected $initialSalesActivity;
    protected $salesActivityOne;
    
    protected $customer;
    protected $customerOne;
    protected $customerTwo;
    protected $customerThree;
    
    protected $customerAssignmentOne;
    protected $customerAssignmentTwo;
    protected $customerAssignmentThree;
    
    protected $greetingAssignmentOne;
    protected $greetingAssignmentTwo;
    protected $greetingAssignmentThree;
    
    protected $factFindingAssignmentOne;
    protected $factFindingAssignmentTwo;
    protected $factFindingAssignmentThree;
    
    protected $strikingAssignmentOne;
    protected $strikingAssignmentTwo;
    protected $strikingAssignmentThree;
    
    protected $salesActivityScheduleOne;
    protected $salesActivityScheduleTwo;
    protected $salesActivityScheduleThree;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('GreetingAssignment')->truncate();
        $this->connection->table('FactFindingAssignment')->truncate();
        $this->connection->table('StrikingAssignment')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
        
        $this->salesActivity = new EntityRecord(SalesActivity::class, 'main');
        $this->salesActivity->columns['duration'] = 20;
        $this->salesActivityOne = new EntityRecord(SalesActivity::class, '1');
        $this->salesActivityOne->columns['duration'] = 30;
        $this->initialSalesActivity = new EntityRecord(SalesActivity::class, 'initial');
        $this->initialSalesActivity->columns['duration'] = 15;
        $this->initialSalesActivity->columns['initial'] = true;
        
        $this->customerOne = new EntityRecord(Customer::class, 1);
        $this->customerTwo = new EntityRecord(Customer::class, 2);
        $this->customerThree = new EntityRecord(Customer::class, 3);
        
        $this->customerAssignmentOne = new EntityRecord(CustomerAssignment::class, 'One');
        $this->customerAssignmentTwo = new EntityRecord(CustomerAssignment::class, 'Two');
        $this->customerAssignmentThree = new EntityRecord(CustomerAssignment::class, 'Three');
        
        $this->greetingAssignmentOne = new EntityRecord(GreetingAssignment::class, 'One');
        $this->greetingAssignmentOne->columns['CustomerAssignment_id'] = $this->customerAssignmentOne->columns['id'];
        $this->greetingAssignmentOne->columns['id'] = $this->customerAssignmentOne->columns['id'];
        $this->greetingAssignmentOne->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->greetingAssignmentOne->columns['Sales_id'] = $this->sales->columns['id'];
        $this->greetingAssignmentTwo = new EntityRecord(GreetingAssignment::class, 'Two');
        $this->greetingAssignmentTwo->columns['CustomerAssignment_id'] = $this->customerAssignmentTwo->columns['id'];
        $this->greetingAssignmentTwo->columns['id'] = $this->customerAssignmentTwo->columns['id'];
        $this->greetingAssignmentTwo->columns['Customer_id'] = $this->customerTwo->columns['id'];
        $this->greetingAssignmentTwo->columns['Sales_id'] = $this->sales->columns['id'];
        $this->greetingAssignmentThree = new EntityRecord(GreetingAssignment::class, 'Three');
        $this->greetingAssignmentThree->columns['CustomerAssignment_id'] = $this->customerAssignmentThree->columns['id'];
        $this->greetingAssignmentThree->columns['id'] = $this->customerAssignmentThree->columns['id'];
        $this->greetingAssignmentThree->columns['Customer_id'] = $this->customerThree->columns['id'];
        $this->greetingAssignmentThree->columns['Sales_id'] = $this->sales->columns['id'];
        
        $this->factFindingAssignmentOne = new EntityRecord(FactFindingAssignment::class, 'One');
        $this->factFindingAssignmentOne->columns['CustomerAssignment_id'] = $this->customerAssignmentOne->columns['id'];
        $this->factFindingAssignmentOne->columns['id'] = $this->customerAssignmentOne->columns['id'];
        $this->factFindingAssignmentOne->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->factFindingAssignmentOne->columns['Sales_id'] = $this->sales->columns['id'];
        $this->factFindingAssignmentTwo = new EntityRecord(FactFindingAssignment::class, 'Two');
        $this->factFindingAssignmentTwo->columns['CustomerAssignment_id'] = $this->customerAssignmentTwo->columns['id'];
        $this->factFindingAssignmentTwo->columns['id'] = $this->customerAssignmentTwo->columns['id'];
        $this->factFindingAssignmentTwo->columns['Customer_id'] = $this->customerTwo->columns['id'];
        $this->factFindingAssignmentTwo->columns['Sales_id'] = $this->sales->columns['id'];
        $this->factFindingAssignmentThree = new EntityRecord(FactFindingAssignment::class, 'Three');
        $this->factFindingAssignmentThree->columns['CustomerAssignment_id'] = $this->customerAssignmentThree->columns['id'];
        $this->factFindingAssignmentThree->columns['id'] = $this->customerAssignmentThree->columns['id'];
        $this->factFindingAssignmentThree->columns['Customer_id'] = $this->customerThree->columns['id'];
        $this->factFindingAssignmentThree->columns['Sales_id'] = $this->sales->columns['id'];
        
        $this->strikingAssignmentOne = new EntityRecord(StrikingAssignment::class, 'One');
        $this->strikingAssignmentOne->columns['CustomerAssignment_id'] = $this->customerAssignmentOne->columns['id'];
        $this->strikingAssignmentOne->columns['id'] = $this->customerAssignmentOne->columns['id'];
        $this->strikingAssignmentOne->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->strikingAssignmentOne->columns['Sales_id'] = $this->sales->columns['id'];
        $this->strikingAssignmentTwo = new EntityRecord(StrikingAssignment::class, 'Two');
        $this->strikingAssignmentTwo->columns['CustomerAssignment_id'] = $this->customerAssignmentTwo->columns['id'];
        $this->strikingAssignmentTwo->columns['id'] = $this->customerAssignmentTwo->columns['id'];
        $this->strikingAssignmentTwo->columns['Customer_id'] = $this->customerTwo->columns['id'];
        $this->strikingAssignmentTwo->columns['Sales_id'] = $this->sales->columns['id'];
        $this->strikingAssignmentThree = new EntityRecord(StrikingAssignment::class, 'Three');
        $this->strikingAssignmentThree->columns['CustomerAssignment_id'] = $this->customerAssignmentThree->columns['id'];
        $this->strikingAssignmentThree->columns['id'] = $this->customerAssignmentThree->columns['id'];
        $this->strikingAssignmentThree->columns['Customer_id'] = $this->customerThree->columns['id'];
        $this->strikingAssignmentThree->columns['Sales_id'] = $this->sales->columns['id'];
        
        $this->salesActivityScheduleOne = new EntityRecord(SalesActivitySchedule::class, 1);
        $this->salesActivityScheduleOne->columns['SalesActivity_id'] = $this->salesActivityOne->columns['id'];
        $this->salesActivityScheduleOne->columns['CustomerAssignment_id'] = $this->customerAssignmentOne->columns['id'];
        $this->salesActivityScheduleOne->columns['startTime'] = (new \DateTime('next monday'))->setTime(10, 0)->format('Y-m-d H') . ":00:00";
        $this->salesActivityScheduleOne->columns['endTime'] = (new \DateTime('next monday'))->setTime(11, 0)->format('Y-m-d H') . ":00:00";
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
        parent::tearDown();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('GreetingAssignment')->truncate();
        $this->connection->table('FactFindingAssignment')->truncate();
        $this->connection->table('StrikingAssignment')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
    }
    
    //
    protected function submitSalesActivitySchedule()
    {
        $this->prepareSalesDependency();
        $this->salesActivity->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation (
    $CustomerAssignment_id: ID!, 
    $SalesActivity_id: ID!, 
    $startTime: DateTimeZ
) {
    submitSalesActivitySchedule (
        CustomerAssignment_id: $CustomerAssignment_id, 
        SalesActivity_id: $SalesActivity_id, 
        startTime: $startTime
    ) {
        id, status, startTime
        salesActivity { id, name, duration }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'CustomerAssignment_id' => $this->greetingAssignmentOne->columns['id'],
            ...$this->submitScheduleRequest
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_submitSalesActivitySchedule_greetingAssignment_200()
    {
        $this->greetingAssignmentOne->insert($this->connection);
        $this->submitSalesActivitySchedule();
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
            'CustomerAssignment_id' => $this->customerAssignmentOne->columns['id'],
            'status' => 'SCHEDULED',
        ]);
    }
    public function test_submitSalesActivitySchedule_factFindingAssignment_200()
    {
        $this->sales->columns['role'] = SalesRole::FACT_FINDER->value;
        $this->factFindingAssignmentOne->insert($this->connection);
        $this->submitSalesActivitySchedule();
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
            'CustomerAssignment_id' => $this->customerAssignmentOne->columns['id'],
            'status' => 'SCHEDULED',
        ]);
    }
    public function test_submitSalesActivitySchedule_strikingAssignment_200()
    {
        $this->sales->columns['role'] = SalesRole::STRIKER->value;
        $this->strikingAssignmentOne->insert($this->connection);
        $this->submitSalesActivitySchedule();
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
            'CustomerAssignment_id' => $this->customerAssignmentOne->columns['id'],
            'status' => 'SCHEDULED',
        ]);
    }
    
    //
    protected function salesActivityScheduleList()
    {
        $this->prepareSalesDependency();
        
        $this->salesActivity->insert($this->connection);
        $this->salesActivityOne->insert($this->connection);
        $this->initialSalesActivity->insert($this->connection);
        
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        
        $this->customerAssignmentOne->insert($this->connection);
        $this->customerAssignmentTwo->insert($this->connection);
        
        $this->greetingAssignmentOne->insert($this->connection);
        $this->greetingAssignmentTwo->insert($this->connection);
        
        $this->salesActivityScheduleOne->insert($this->connection);
        $this->salesActivityScheduleTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query {
    salesActivityScheduleList {
        list {
            id, status, startTime, endTime
            greetingAssignment {
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
    public function test_salesActivityScheduleList_200()
    {
        $this->salesActivityScheduleList();
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->salesActivityScheduleOne->columns['id'],
                    'status' => $this->salesActivityScheduleOne->columns['status'],
                    'startTime' => $this->jakartaDateTimeFormat($this->salesActivityScheduleOne->columns['startTime']),
                    'endTime' => $this->jakartaDateTimeFormat($this->salesActivityScheduleOne->columns['endTime']),
                    'greetingAssignment' => [
                        'id' => $this->greetingAssignmentOne->columns['id'],
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
                    'greetingAssignment' => [
                        'id' => $this->greetingAssignmentTwo->columns['id'],
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
    protected function salesActivityScheduleDetail()
    {
        $this->prepareSalesDependency();
        
        $this->salesActivity->insert($this->connection);
        
        $this->customerOne->insert($this->connection);
        
        $this->customerAssignmentOne->insert($this->connection);
        $this->greetingAssignmentOne->insert($this->connection);
        
        $this->salesActivityScheduleOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $id: ID!) {
    salesActivityScheduleDetail ( id: $id ) {
        id, status, startTime, endTime
        greetingAssignment {
            id, 
            customer { id, name }
        }
    }
}
_QUERY;
        $this->graphqlVariables['id'] = $this->salesActivityScheduleOne->columns['id'];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_salesActivityScheduleDetail_200()
    {
        $this->salesActivityScheduleDetail();
        $this->seeJsonContains([
            'id' => $this->salesActivityScheduleOne->columns['id'],
            'status' => $this->salesActivityScheduleOne->columns['status'],
            'startTime' => $this->jakartaDateTimeFormat($this->salesActivityScheduleOne->columns['startTime']),
            'endTime' => $this->jakartaDateTimeFormat($this->salesActivityScheduleOne->columns['endTime']),
            'greetingAssignment' => [
                'id' => $this->greetingAssignmentOne->columns['id'],
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
        
        $this->greetingAssignmentOne->insert($this->connection);
        $this->greetingAssignmentTwo->insert($this->connection);
        $this->greetingAssignmentThree->insert($this->connection);
        
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
    protected function viewAllOngoingSchedule()
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
        
        $this->greetingAssignmentOne->insert($this->connection);
        $this->greetingAssignmentTwo->insert($this->connection);
        $this->greetingAssignmentThree->insert($this->connection);
        
        $this->salesActivityScheduleOne->columns['status'] = SalesActivityScheduleStatus::COMPLETED;
        $this->salesActivityScheduleTwo->columns['status'] = SalesActivityScheduleStatus::SCHEDULED;
        $this->salesActivityScheduleThree->columns['status'] = SalesActivityScheduleStatus::COMPLETED;
        $this->salesActivityScheduleOne->insert($this->connection);
        $this->salesActivityScheduleTwo->insert($this->connection);
        $this->salesActivityScheduleThree->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query {
    viewAllOngoingSchedule {
        id, status, startTime, endTime
        greetingAssignment {
            id, 
            customer { id, name }
        }
    }
}
_QUERY;
        $this->graphqlVariables = [];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_viewAllOngoingSchedule_200()
    {
        $this->viewAllOngoingSchedule();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            [
                'id' => $this->salesActivityScheduleTwo->columns['id'],
                'status' => $this->salesActivityScheduleTwo->columns['status'],
                'startTime' => $this->jakartaDateTimeFormat($this->salesActivityScheduleTwo->columns['startTime']),
                'endTime' => $this->jakartaDateTimeFormat($this->salesActivityScheduleTwo->columns['endTime']),
                'greetingAssignment' => [
                    'id' => $this->greetingAssignmentTwo->columns['id'],
                    'customer' => [
                        'id' => $this->customerTwo->columns['id'],
                        'name' => $this->customerTwo->columns['name'],
                    ],
                ],
            ],
        ]);
    }
    
}
