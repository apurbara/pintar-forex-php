<?php

namespace Tests\Http\GraphQL\ManagerBC;

use Company\Domain\Model\Personnel;
use Company\Domain\Model\Personnel\Manager\Sales;
use Manager\Domain\Model\AreaStructure\Area\Customer;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;
use Tests\Http\Record\EntityRecord;

class AssignedCustomerControllerTest extends ManagerBCTestCase
{
    protected EntityRecord $customerOne;
    protected EntityRecord $customerTwo;
    protected EntityRecord $customerThree;
    protected EntityRecord $customerFour;
    
    protected EntityRecord $personnelOne;
    protected EntityRecord $personnelTwo;
    
    protected EntityRecord $salesOne;
    protected EntityRecord $salesTwo;
    
    protected EntityRecord $assignedCustomer_11;
    protected EntityRecord $assignedCustomer_12;
    protected EntityRecord $assignedCustomer_23;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('AssignedCustomer')->truncate();
        
        //
        $this->customerOne = new EntityRecord(Customer::class, 1);
        $this->customerTwo = new EntityRecord(Customer::class, 2);
        $this->customerThree = new EntityRecord(Customer::class, 3);
        $this->customerFour = new EntityRecord(Customer::class, 4);
        
        $this->personnelOne = new EntityRecord(Personnel::class, 1);
        $this->personnelTwo = new EntityRecord(Personnel::class, 2);
        
        $this->salesOne = new EntityRecord(Sales::class, 1);
        $this->salesOne->columns['Personnel_id'] = $this->personnelOne->columns['id'];
        $this->salesOne->columns['Manager_id'] = $this->manager->columns['id'];
        $this->salesTwo = new EntityRecord(Sales::class, 2);
        $this->salesTwo->columns['Personnel_id'] = $this->personnelTwo->columns['id'];
        $this->salesTwo->columns['Manager_id'] = $this->manager->columns['id'];
        
        $this->assignedCustomer_11 = new EntityRecord(AssignedCustomer::class, 11);
        $this->assignedCustomer_11->columns['Sales_id'] = $this->salesOne->columns['id'];
        $this->assignedCustomer_11->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->assignedCustomer_12 = new EntityRecord(AssignedCustomer::class, 12);
        $this->assignedCustomer_12->columns['Sales_id'] = $this->salesOne->columns['id'];
        $this->assignedCustomer_12->columns['Customer_id'] = $this->customerTwo->columns['id'];
        $this->assignedCustomer_23 = new EntityRecord(AssignedCustomer::class, 23);
        $this->assignedCustomer_23->columns['Sales_id'] = $this->salesTwo->columns['id'];
        $this->assignedCustomer_23->columns['Customer_id'] = $this->customerThree->columns['id'];
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('AssignedCustomer')->truncate();
    }
    
    //
    protected function assignedCustomerDetail()
    {
        $this->prepareManagerDependency();
        $this->customerOne->insert($this->connection);
        $this->personnelOne->insert($this->connection);
        $this->salesOne->insert($this->connection);
        
        $this->assignedCustomer_11->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $managerId: ID!, $id: ID) {
    manager ( managerId: $managerId ) {
        assignedCustomerDetail ( id: $id ) {
            id, 
            sales { personnel { name } }
            customer { name, phone }
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'managerId' => $this->manager->columns['id'],
            'id' => $this->assignedCustomer_11->columns['id'],
        ];
        $this->postGraphqlRequest($this->personnel->token);
    }
    public function test_assignedCustomerDetail_200()
    {
        $this->assignedCustomerDetail();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'id' => $this->assignedCustomer_11->columns['id'],
            'sales' => [
                'personnel' => [
                    'name' => $this->personnelOne->columns['name'],
                ],
            ],
            'customer' => [
                'name' => $this->customerOne->columns['name'],
                'phone' => $this->customerOne->columns['phone'],
            ],
        ]);
    }
    
    //
    protected function assignedCustomerList()
    {
        $this->prepareManagerDependency();
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerThree->insert($this->connection);
        
        $this->personnelOne->insert($this->connection);
        $this->personnelTwo->insert($this->connection);
        
        $this->salesTwo->insert($this->connection);
        $this->salesOne->insert($this->connection);
        
        $this->assignedCustomer_11->insert($this->connection);
        $this->assignedCustomer_12->insert($this->connection);
        $this->assignedCustomer_23->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $managerId: ID!) {
    manager ( managerId: $managerId ) {
        assignedCustomerList {
            list {
                id, 
                sales { personnel { name } }
                customer { name, phone }
            }
            cursorLimit { total }
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'managerId' => $this->manager->columns['id'],
        ];
        $this->postGraphqlRequest($this->personnel->token);
    }
    public function test_assignedCustomerList_200()
    {
        $this->assignedCustomerList();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->assignedCustomer_11->columns['id'],
                    'sales' => [
                        'personnel' => [
                            'name' => $this->personnelOne->columns['name'],
                        ],
                    ],
                    'customer' => [
                        'name' => $this->customerOne->columns['name'],
                        'phone' => $this->customerOne->columns['phone'],
                    ],
                ],
                [
                    'id' => $this->assignedCustomer_12->columns['id'],
                    'sales' => [
                        'personnel' => [
                            'name' => $this->personnelOne->columns['name'],
                        ],
                    ],
                    'customer' => [
                        'name' => $this->customerTwo->columns['name'],
                        'phone' => $this->customerTwo->columns['phone'],
                    ],
                ],
                [
                    'id' => $this->assignedCustomer_23->columns['id'],
                    'sales' => [
                        'personnel' => [
                            'name' => $this->personnelTwo->columns['name'],
                        ],
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
}
