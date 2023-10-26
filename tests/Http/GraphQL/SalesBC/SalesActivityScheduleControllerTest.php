<?php

namespace Tests\Http\GraphQL\SalesBC;

use Company\Domain\Model\SalesActivity;
use DateTime;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule;
use Tests\Http\Record\EntityRecord;

class SalesActivityScheduleControllerTest extends SalesBCTestCase
{
    protected $salesActivity;
    
    protected $customer;
    protected $customerOne;
    protected $customerTwo;
    
    protected $assignedCustomer;
    protected $assignedCustomerOne;
    protected $assignedCustomerTwo;
    
    protected $salesActivityScheduleOne;
    protected $salesActivityScheduleTwo;
    
    protected $submitScheduleRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('AssignedCustomer')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
        
        $this->salesActivity = new EntityRecord(SalesActivity::class, 'main');
        
        $this->customer = new EntityRecord(Customer::class, 'main');
        $this->customerOne = new EntityRecord(Customer::class, 1);
        $this->customerTwo = new EntityRecord(Customer::class, 2);
        
        $this->assignedCustomer = new EntityRecord(AssignedCustomer::class, 'main');
        $this->assignedCustomer->columns['Customer_id'] = $this->customer->columns['id'];
        $this->assignedCustomer->columns['Sales_id'] = $this->sales->columns['id'];
        $this->assignedCustomerOne = new EntityRecord(AssignedCustomer::class, 1);
        $this->assignedCustomerOne->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->assignedCustomerOne->columns['Sales_id'] = $this->sales->columns['id'];
        $this->assignedCustomerTwo = new EntityRecord(AssignedCustomer::class, 2);
        $this->assignedCustomerTwo->columns['Customer_id'] = $this->customerTwo->columns['id'];
        $this->assignedCustomerTwo->columns['Sales_id'] = $this->sales->columns['id'];
        
        $this->salesActivityScheduleOne = new EntityRecord(SalesActivitySchedule::class, 1);
        $this->salesActivityScheduleOne->columns['SalesActivity_id'] = $this->salesActivity->columns['id'];
        $this->salesActivityScheduleOne->columns['AssignedCustomer_id'] = $this->assignedCustomerOne->columns['id'];
        $this->salesActivityScheduleTwo = new EntityRecord(SalesActivitySchedule::class, 2);
        $this->salesActivityScheduleTwo->columns['SalesActivity_id'] = $this->salesActivity->columns['id'];
        $this->salesActivityScheduleTwo->columns['AssignedCustomer_id'] = $this->assignedCustomerTwo->columns['id'];
        
        $this->submitScheduleRequest = [
            'salesActivityId' => $this->salesActivity->columns['id'],
            'startTime' => 'next week',
        ];
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('AssignedCustomer')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
    }
    
    //
    protected function submitSchedule()
    {
        $this->prepareSalesDependency();
        $this->salesActivity->insert($this->connection);
        $this->assignedCustomer->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $salesId: ID!, $assignedCustomerId: ID!, $salesActivityId: ID!, $startTime: DateTimeZ ) {
    sales ( salesId: $salesId ) {
        assignedCustomer ( assignedCustomerId: $assignedCustomerId) {
            submitSalesActivitySchedule ( salesActivityId: $salesActivityId, startTime: $startTime ) {
                id, status, startTime
                salesActivity { id, name, duration }
            }
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'salesId' => $this->sales->columns['id'],
            'assignedCustomerId' => $this->assignedCustomer->columns['id'],
            ...$this->submitScheduleRequest
        ];
        $this->postGraphqlRequest($this->personnel->token);
    }
    public function test_submitSchedule_200()
    {
        $this->submitSchedule();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'status' => 'SCHEDULED',
            'startTime' => $this->jakartaDateTimeFormat((new DateTime($this->submitScheduleRequest['startTime']))->format('Y-m-d H') . ":00:00"),
            'salesActivity' => [
                'id' => $this->submitScheduleRequest['salesActivityId'],
                'name' => $this->salesActivity->columns['name'],
                'duration' => $this->salesActivity->columns['duration'],
            ],
        ]);
        
        $this->seeInDatabase('SalesActivitySchedule', [
            'SalesActivity_id' => $this->salesActivity->columns['id'],
            'AssignedCustomer_id' => $this->assignedCustomer->columns['id'],
            'status' => 'SCHEDULED',
        ]);
    }
    
    //
    protected function viewList()
    {
        $this->prepareSalesDependency();
        
        $this->salesActivity->insert($this->connection);
        
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        
        $this->assignedCustomerOne->insert($this->connection);
        $this->assignedCustomerTwo->insert($this->connection);
        
        $this->salesActivityScheduleOne->insert($this->connection);
        $this->salesActivityScheduleTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $salesId: ID!) {
    sales ( salesId: $salesId ) {
        salesActivityScheduleList {
            list {
                id, status, startTime, endTime
                assignedCustomer {
                    id, 
                    customer { id, name }
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
                    'id' => $this->salesActivityScheduleOne->columns['id'],
                    'status' => $this->salesActivityScheduleOne->columns['status'],
                    'startTime' => $this->jakartaDateTimeFormat($this->salesActivityScheduleOne->columns['startTime']),
                    'endTime' => $this->jakartaDateTimeFormat($this->salesActivityScheduleOne->columns['endTime']),
                    'assignedCustomer' => [
                        'id' => $this->assignedCustomerOne->columns['id'],
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
                    'assignedCustomer' => [
                        'id' => $this->assignedCustomerTwo->columns['id'],
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
    protected function viewDetail()
    {
        $this->prepareSalesDependency();
        
        $this->salesActivity->insert($this->connection);
        
        $this->customerOne->insert($this->connection);
        
        $this->assignedCustomerOne->insert($this->connection);
        
        $this->salesActivityScheduleOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $salesId: ID!, $id: ID!) {
    sales ( salesId: $salesId ) {
        salesActivityScheduleDetail ( salesActivityScheduleId: $id ) {
            id, status, startTime, endTime
            assignedCustomer {
                id, 
                customer { id, name }
            }
        }
    }
}
_QUERY;
        $this->graphqlVariables['salesId'] = $this->sales->columns['id'];
        $this->graphqlVariables['id'] = $this->salesActivityScheduleOne->columns['id'];
        $this->postGraphqlRequest($this->personnel->token);
    }
    public function test_viewDetail_200()
    {
        $this->viewDetail();
        $this->seeJsonContains([
            'id' => $this->salesActivityScheduleOne->columns['id'],
            'status' => $this->salesActivityScheduleOne->columns['status'],
            'startTime' => $this->jakartaDateTimeFormat($this->salesActivityScheduleOne->columns['startTime']),
            'endTime' => $this->jakartaDateTimeFormat($this->salesActivityScheduleOne->columns['endTime']),
            'assignedCustomer' => [
                'id' => $this->assignedCustomerOne->columns['id'],
                'customer' => [
                    'id' => $this->customerOne->columns['id'],
                    'name' => $this->customerOne->columns['name'],
                ],
            ],
        ]);
    }
}
