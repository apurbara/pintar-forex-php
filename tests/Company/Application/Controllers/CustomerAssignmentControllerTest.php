<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\Customer;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\Manager\Sales;
use Company\Domain\Model\Manager\Sales\CustomerAssignment;
use Company\Domain\Model\SalesActivity;
use Tests\Company\Application\Controllers\CompanyControllerTestCase;
use Tests\resources\Application\EntityRecord;

class CustomerAssignmentControllerTest extends CompanyControllerTestCase
{
    protected EntityRecord $customerOne;
    protected EntityRecord $customerTwo;
    protected EntityRecord $customerThree;
    protected EntityRecord $customerFour;
    
    protected EntityRecord $salesOne;
    protected EntityRecord $salesTwo;
    
    protected EntityRecord $customerAssignment_11;
    protected EntityRecord $customerAssignment_12;
    protected EntityRecord $customerAssignment_23;
    
    protected EntityRecord $customerJourneyInitial;
    protected EntityRecord $salesActivityInitial;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('CustomerJourney')->truncate();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
        
        //
        $this->customerOne = new EntityRecord(Customer::class, 1);
        $this->customerTwo = new EntityRecord(Customer::class, 2);
        $this->customerThree = new EntityRecord(Customer::class, 3);
        $this->customerFour = new EntityRecord(Customer::class, 4);
        
        $this->salesOne = new EntityRecord(Sales::class, 1);
        $this->salesTwo = new EntityRecord(Sales::class, 2);
        
        $this->customerJourneyInitial = new EntityRecord(CustomerJourney::class, 'initial');
        $this->customerJourneyInitial->columns['initial'] = true;
        
        $this->customerAssignment_11 = new EntityRecord(CustomerAssignment::class, 11);
        $this->customerAssignment_11->columns['Sales_id'] = $this->salesOne->columns['id'];
        $this->customerAssignment_11->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->customerAssignment_11->columns['CustomerJourney_id'] = $this->customerJourneyInitial->columns['id'];
        $this->customerAssignment_12 = new EntityRecord(CustomerAssignment::class, 12);
        $this->customerAssignment_12->columns['Sales_id'] = $this->salesOne->columns['id'];
        $this->customerAssignment_12->columns['Customer_id'] = $this->customerTwo->columns['id'];
        $this->customerAssignment_12->columns['CustomerJourney_id'] = $this->customerJourneyInitial->columns['id'];
        $this->customerAssignment_23 = new EntityRecord(CustomerAssignment::class, 23);
        $this->customerAssignment_23->columns['Sales_id'] = $this->salesTwo->columns['id'];
        $this->customerAssignment_23->columns['Customer_id'] = $this->customerThree->columns['id'];
        $this->customerAssignment_23->columns['CustomerJourney_id'] = $this->customerJourneyInitial->columns['id'];
        
        $this->salesActivityInitial = new EntityRecord(SalesActivity::class, 'initial');
        $this->salesActivityInitial->columns['initial'] = true;
        $this->salesActivityInitial->columns['duration'] = 20;
    }
    protected function tearDown(): void
    {
//        parent::tearDown();
//        $this->connection->table('Sales')->truncate();
//        $this->connection->table('Customer')->truncate();
//        $this->connection->table('CustomerAssignment')->truncate();
//        $this->connection->table('CustomerJourney')->truncate();
//        $this->connection->table('SalesActivity')->truncate();
//        $this->connection->table('SalesActivitySchedule')->truncate();
    }
    
    //
    protected function customerAssignmentDetail()
    {
        $this->prepareManagerDependency();
        $this->customerOne->insert($this->connection);
        $this->salesOne->insert($this->connection);
        
        $this->customerAssignment_11->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $id: ID) {
    customerAssignmentDetail ( id: $id ) {
        id, 
        sales { name }
        customer { name, phone }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->customerAssignment_11->columns['id'],
        ];
        $this->postGraphqlRequest($this->manager->token);
    }
    public function test_customerAssignmentDetail_200()
    {
        $this->customerAssignmentDetail();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'id' => $this->customerAssignment_11->columns['id'],
            'sales' => [
                    'name' => $this->salesOne->columns['name'],
            ],
            'customer' => [
                'name' => $this->customerOne->columns['name'],
                'phone' => $this->customerOne->columns['phone'],
            ],
        ]);
    }
    
    //
    protected function customerAssignmentList()
    {
        $this->prepareManagerDependency();
        $this->customerJourneyInitial->insert($this->connection);
        
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerThree->insert($this->connection);
        
        $this->salesTwo->insert($this->connection);
        $this->salesOne->insert($this->connection);
        
        $this->customerAssignment_11->insert($this->connection);
        $this->customerAssignment_12->insert($this->connection);
        $this->customerAssignment_23->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $filters: [FilterInput] ) {
    customerAssignmentList ( filters: $filters ) {
        list {
            id, 
            sales { name }
            customer { name, phone }
        }
        cursorLimit { total }
    }
}
_QUERY;
        $this->graphqlVariables = $this->getPaginationInput();
        $this->postGraphqlRequest($this->manager->token);
    }
    public function test_customerAssignmentList_200()
    {
        $this->customerAssignmentList();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->customerAssignment_11->columns['id'],
                    'sales' => [
                        'name' => $this->salesOne->columns['name'],
                    ],
                    'customer' => [
                        'name' => $this->customerOne->columns['name'],
                        'phone' => $this->customerOne->columns['phone'],
                    ],
                ],
                [
                    'id' => $this->customerAssignment_12->columns['id'],
                    'sales' => [
                        'name' => $this->salesOne->columns['name'],
                    ],
                    'customer' => [
                        'name' => $this->customerTwo->columns['name'],
                        'phone' => $this->customerTwo->columns['phone'],
                    ],
                ],
                [
                    'id' => $this->customerAssignment_23->columns['id'],
                    'sales' => [
                        'name' => $this->salesTwo->columns['name'],
                    ],
                    'customer' => [
                        'name' => $this->customerThree->columns['name'],
                        'phone' => $this->customerThree->columns['phone'],
                    ],
                ],
            ],
            'cursorLimit' => ['total' => 3],
        ]);
    }
    public function test_customerAssignmentList_hasSalesActivityScheduleFilter()
    {
        $this->filters = [
            ['column' => 'hasSalesActivitySchedule', 'value' => false],
        ];
        $this->customerAssignmentList();
        $this->seeStatusCode(200);
        $this->seeJsonContains(['total' => 3]);
    }
    
    //
    protected function viewCustomerAssignmentCount()
    {
        $this->prepareManagerDependency();
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerThree->insert($this->connection);
        
        $this->salesTwo->insert($this->connection);
        $this->salesOne->insert($this->connection);
        
        $this->customerAssignment_11->insert($this->connection);
        $this->customerAssignment_12->insert($this->connection);
        $this->customerAssignment_23->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ($filters: [FilterInput]) {
    viewCustomerAssignmentCount(filters: $filters)
}
_QUERY;
        $this->graphqlVariables = $this->getPaginationInput();
        $this->postGraphqlRequest($this->manager->token);
    }
    public function test_viewCustomerAssignmentCount_200()
    {
        $this->viewCustomerAssignmentCount();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains(['viewCustomerAssignmentCount' => 3]);
    }
    
}
