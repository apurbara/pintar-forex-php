<?php

namespace Tests\Http\GraphQL\SalesBC;

use Company\Domain\Model\SalesActivity;
use DateTime;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\ScheduledSalesActivity;
use Tests\Http\Record\EntityRecord;

class ScheduledSalesActivityControllerTest extends SalesBCTestCase
{
    protected $salesActivity;
    
    protected $customer;
    protected $customerOne;
    protected $customerTwo;
    
    protected $assignedCustomer;
    protected $assignedCustomerOne;
    protected $assignedCustomerTwo;
    
    protected $scheduledSalesActivityOne;
    protected $scheduledSalesActivityTwo;
    
    protected $submitScheduleRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('AssignedCustomer')->truncate();
        $this->connection->table('ScheduledSalesActivity')->truncate();
        
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
        
        $this->scheduledSalesActivityOne = new EntityRecord(ScheduledSalesActivity::class, 1);
        $this->scheduledSalesActivityOne->columns['SalesActivity_id'] = $this->salesActivity->columns['id'];
        $this->scheduledSalesActivityOne->columns['AssignedCustomer_id'] = $this->assignedCustomerOne->columns['id'];
        $this->scheduledSalesActivityTwo = new EntityRecord(ScheduledSalesActivity::class, 2);
        $this->scheduledSalesActivityTwo->columns['SalesActivity_id'] = $this->salesActivity->columns['id'];
        $this->scheduledSalesActivityTwo->columns['AssignedCustomer_id'] = $this->assignedCustomerTwo->columns['id'];
        
        $this->submitScheduleRequest = [
            'salesActivityId' => $this->salesActivity->columns['id'],
            'startTime' => 'next week',
        ];
    }
    protected function tearDown(): void
    {
//        parent::tearDown();
//        $this->connection->table('SalesActivity')->truncate();
//        $this->connection->table('Customer')->truncate();
//        $this->connection->table('AssignedCustomer')->truncate();
//        $this->connection->table('ScheduledSalesActivity')->truncate();
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
        
        $this->seeInDatabase('ScheduledSalesActivity', [
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
        
        $this->scheduledSalesActivityOne->insert($this->connection);
        $this->scheduledSalesActivityTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $salesId: ID!) {
    sales ( salesId: $salesId ) {
        scheduledSalesActivityList {
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
                    'id' => $this->scheduledSalesActivityOne->columns['id'],
                    'status' => $this->scheduledSalesActivityOne->columns['status'],
                    'startTime' => $this->jakartaDateTimeFormat($this->scheduledSalesActivityOne->columns['startTime']),
                    'endTime' => $this->jakartaDateTimeFormat($this->scheduledSalesActivityOne->columns['endTime']),
                    'assignedCustomer' => [
                        'id' => $this->assignedCustomerOne->columns['id'],
                        'customer' => [
                            'id' => $this->customerOne->columns['id'],
                            'name' => $this->customerOne->columns['name'],
                        ],
                    ],
                ],
                [
                    'id' => $this->scheduledSalesActivityTwo->columns['id'],
                    'status' => $this->scheduledSalesActivityTwo->columns['status'],
                    'startTime' => $this->jakartaDateTimeFormat($this->scheduledSalesActivityTwo->columns['startTime']),
                    'endTime' => $this->jakartaDateTimeFormat($this->scheduledSalesActivityTwo->columns['endTime']),
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
        
        $this->scheduledSalesActivityOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $salesId: ID!, $id: ID!) {
    sales ( salesId: $salesId ) {
        scheduledSalesActivityDetail ( scheduledSalesActivityId: $id ) {
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
        $this->graphqlVariables['id'] = $this->scheduledSalesActivityOne->columns['id'];
        $this->postGraphqlRequest($this->personnel->token);
    }
    public function test_viewDetail_200()
    {
        $this->viewDetail();
        $this->seeJsonContains([
            'id' => $this->scheduledSalesActivityOne->columns['id'],
            'status' => $this->scheduledSalesActivityOne->columns['status'],
            'startTime' => $this->jakartaDateTimeFormat($this->scheduledSalesActivityOne->columns['startTime']),
            'endTime' => $this->jakartaDateTimeFormat($this->scheduledSalesActivityOne->columns['endTime']),
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
