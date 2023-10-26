<?php

namespace Tests\Http\GraphQL\SalesBC;

use Company\Domain\Model\AreaStructure\Area;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Tests\Http\Record\EntityRecord;

class AssignedCustomerControllerTest extends SalesBCTestCase
{
    protected $customerOne;
    protected $customerTwo;
    protected $assignedCustomerOne;
    protected $assignedCustomerTwo;
    
    protected $registerNewCustomerRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('AssignedCustomer')->truncate();
        
        $this->area = new EntityRecord(Area::class, 'main');
        
        $this->customerOne = new EntityRecord(Customer::class, 1);
        $this->customerOne->columns['Area_id'] = $this->area->columns['id'];
        $this->customerTwo = new EntityRecord(Customer::class, 2);
        $this->customerTwo->columns['Area_id'] = $this->area->columns['id'];
        
        $this->assignedCustomerOne = new EntityRecord(AssignedCustomer::class, 1);
        $this->assignedCustomerOne->columns['Sales_id'] = $this->sales->columns['id'];
        $this->assignedCustomerOne->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->assignedCustomerTwo = new EntityRecord(AssignedCustomer::class, 2);
        $this->assignedCustomerTwo->columns['Sales_id'] = $this->sales->columns['id'];
        $this->assignedCustomerTwo->columns['Customer_id'] = $this->customerTwo->columns['id'];
        
        $this->registerNewCustomerRequest = [
            'areaId' => $this->area->columns['id'],
            'name' => 'new customer name',
            'email' => 'newCustomer@email.org',
        ];
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('AssignedCustomer')->truncate();
    }
    
    //
    protected function registerNewCustomer()
    {
        $this->prepareSalesDependency();
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $salesId: ID!, $areaId: ID!, $name: String, $email: String ) {
    sales ( salesId: $salesId ) {
        registerNewCustomer ( areaId: $areaId, name: $name, email: $email ) {
            id, disabled, createdTime
            customer {
                name, email
                area { id, name }
            }
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
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'customer' => [
                'name' => $this->registerNewCustomerRequest['name'],
                'email' => $this->registerNewCustomerRequest['email'],
                'area' => [
                    'id' => $this->registerNewCustomerRequest['areaId'],
                    'name' => $this->area->columns['name'],
                ],
            ],
        ]);
        
        $this->seeInDatabase('AssignedCustomer', [
            'Sales_id' => $this->sales->columns['id'],
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
        ]);
        
        $this->seeInDatabase('Customer', [
            'Area_id' => $this->area->columns['id'],
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'name' => $this->registerNewCustomerRequest['name'],
            'email' => $this->registerNewCustomerRequest['email'],
        ]);
    }
    
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
                id, disabled, createdTime
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
                    'disabled' => $this->assignedCustomerOne->columns['disabled'],
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
                    'disabled' => $this->assignedCustomerTwo->columns['disabled'],
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
    
    protected function viewDetail()
    {
        $this->prepareSalesDependency();
        $this->customerOne->insert($this->connection);
        
        $this->assignedCustomerOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $salesId: ID!, $assignedCustomerId: ID!) {
    sales ( salesId: $salesId ) {
        assignedCustomerDetail ( assignedCustomerId: $assignedCustomerId ) {
            id, disabled, createdTime
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
            'disabled' => $this->assignedCustomerOne->columns['disabled'],
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
}
