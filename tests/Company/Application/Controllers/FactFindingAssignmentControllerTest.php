<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\Customer;
use Company\Domain\Model\Manager\Sales;
use Company\Domain\Model\Manager\Sales\CustomerAssignment;
use Company\Domain\Model\Manager\Sales\FactFindingAssignment;
use Company\Domain\Service\CustomerAssignmentDistributionServiceBuilder;
use Shared\Domain\Enum\CustomerStatus;
use Shared\Domain\Enum\SalesRole;
use Tests\Company\Application\Controllers\CompanyControllerTestCase;
use Tests\resources\Application\EntityRecord;

class FactFindingAssignmentControllerTest extends CompanyControllerTestCase
{
    protected EntityRecord $customerOne;
    protected EntityRecord $customerTwo;
    protected EntityRecord $customerThree;
    protected EntityRecord $customerFour;
    
    protected EntityRecord $salesOne;
    protected EntityRecord $salesTwo;
    
    protected EntityRecord $factFindingAssignment_11, $customerAssignment_11;
    protected EntityRecord $factFindingAssignment_12, $customerAssignment_12;
    protected EntityRecord $factFindingAssignment_23, $customerAssignment_23;
    
    protected $assignedMultipleCustomerToMultipleSalesInput = [];
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('FactFindingAssignment')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        
        //
        $this->customerOne = new EntityRecord(Customer::class, 1);
        $this->customerOne->columns['status'] = CustomerStatus::FACT_FINDING_REQUIRED->value;
        $this->customerTwo = new EntityRecord(Customer::class, 2);
        $this->customerTwo->columns['status'] = CustomerStatus::FACT_FINDING_REQUIRED->value;
        $this->customerThree = new EntityRecord(Customer::class, 3);
        $this->customerThree->columns['status'] = CustomerStatus::FACT_FINDING_REQUIRED->value;
        $this->customerFour = new EntityRecord(Customer::class, 4);
        $this->customerFour->columns['status'] = CustomerStatus::FACT_FINDING_REQUIRED->value;
        
        $this->salesOne = new EntityRecord(Sales::class, 1);
        $this->salesOne->columns['role'] = SalesRole::FACT_FINDER->value;
        $this->salesTwo = new EntityRecord(Sales::class, 2);
        $this->salesTwo->columns['role'] = SalesRole::FACT_FINDER->value;
        
        $this->customerAssignment_11 = new EntityRecord(CustomerAssignment::class, 11);
        $this->factFindingAssignment_11 = new EntityRecord(FactFindingAssignment::class, 11);
        $this->factFindingAssignment_11->columns['CustomerAssignment_id'] = $this->customerAssignment_11->columns['id'];
        $this->factFindingAssignment_11->columns['id'] = $this->customerAssignment_11->columns['id'];
        $this->factFindingAssignment_11->columns['Sales_id'] = $this->salesOne->columns['id'];
        $this->factFindingAssignment_11->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->customerAssignment_12 = new EntityRecord(CustomerAssignment::class, 12);
        $this->factFindingAssignment_12 = new EntityRecord(FactFindingAssignment::class, 12);
        $this->factFindingAssignment_12->columns['CustomerAssignment_id'] = $this->customerAssignment_12->columns['id'];
        $this->factFindingAssignment_12->columns['id'] = $this->customerAssignment_12->columns['id'];
        $this->factFindingAssignment_12->columns['Sales_id'] = $this->salesOne->columns['id'];
        $this->factFindingAssignment_12->columns['Customer_id'] = $this->customerTwo->columns['id'];
        $this->customerAssignment_23 = new EntityRecord(CustomerAssignment::class, 23);
        $this->factFindingAssignment_23 = new EntityRecord(FactFindingAssignment::class, 23);
        $this->factFindingAssignment_23->columns['CustomerAssignment_id'] = $this->customerAssignment_23->columns['id'];
        $this->factFindingAssignment_23->columns['id'] = $this->customerAssignment_23->columns['id'];
        $this->factFindingAssignment_23->columns['Sales_id'] = $this->salesTwo->columns['id'];
        $this->factFindingAssignment_23->columns['Customer_id'] = $this->customerThree->columns['id'];
        
        $this->assignedMultipleCustomerToMultipleSalesInput = [
            'distributionStrategy' => CustomerAssignmentDistributionServiceBuilder::EVEN_DISTRIBUTION,
            'salesList' => [
                $this->salesOne->columns['id'],
                $this->salesTwo->columns['id'],
            ],
            'customerList' => [
                $this->customerOne->columns['id'],
                $this->customerTwo->columns['id'],
                $this->customerThree->columns['id'],
                $this->customerFour->columns['id'],
            ],
        ];
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('FactFindingAssignment')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
    }
    
    //
    protected function assignFactFindingActivityOfCustomerListToSales()
    {
        $this->prepareAdminDependency();
        
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerThree->insert($this->connection);
        $this->customerFour->insert($this->connection);
        
        $this->salesOne->insert($this->connection);
        $this->salesTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( 
    $salesList: [ID], $customerList: [ID], $distributionStrategy: String,
) {
    assignFactFindingActivityOfCustomerListToSales (
        salesList: $salesList, customerList: $customerList, distributionStrategy: $distributionStrategy, 
    )
}
_QUERY;
        $this->graphqlVariables = $this->assignedMultipleCustomerToMultipleSalesInput;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_assignedMultipleCustomerToMultipleSales_distributeAssignment()
    {
$this->disableExceptionHandling();
        $this->assignFactFindingActivityOfCustomerListToSales();
        $this->seeStatusCode(200);
        
        $this->seeInDatabase('FactFindingAssignment', [
            'Sales_id' => $this->salesOne->columns['id'],
            'Customer_id' => $this->customerOne->columns['id'],
        ]);
        
        $this->seeInDatabase('FactFindingAssignment', [
            'Sales_id' => $this->salesOne->columns['id'],
            'Customer_id' => $this->customerThree->columns['id'],
        ]);
        
        $this->seeInDatabase('FactFindingAssignment', [
            'Sales_id' => $this->salesTwo->columns['id'],
            'Customer_id' => $this->customerTwo->columns['id'],
        ]);
        
        $this->seeInDatabase('FactFindingAssignment', [
            'Sales_id' => $this->salesTwo->columns['id'],
            'Customer_id' => $this->customerFour->columns['id'],
        ]);
    }
    
    //
    protected function factFindingAssignmentDetail()
    {
        $this->prepareAdminDependency();
        $this->customerOne->insert($this->connection);
        $this->salesOne->insert($this->connection);
        
        $this->customerAssignment_11->insert($this->connection);
        $this->factFindingAssignment_11->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $id: ID) {
    factFindingAssignmentDetail ( id: $id ) {
        id, 
        sales { name }
        customer { name, phone }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->factFindingAssignment_11->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_factFindingAssignmentDetail_200()
    {
        $this->factFindingAssignmentDetail();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'id' => $this->factFindingAssignment_11->columns['id'],
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
    protected function factFindingAssignmentList()
    {
        $this->prepareAdminDependency();
        
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerThree->insert($this->connection);
        
        $this->salesTwo->insert($this->connection);
        $this->salesOne->insert($this->connection);
        
        $this->customerAssignment_11->insert($this->connection);
        $this->customerAssignment_12->insert($this->connection);
        $this->customerAssignment_23->insert($this->connection);
        
        $this->factFindingAssignment_11->insert($this->connection);
        $this->factFindingAssignment_12->insert($this->connection);
        $this->factFindingAssignment_23->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $filters: [FilterInput] ) {
    factFindingAssignmentList ( filters: $filters ) {
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
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_factFindingAssignmentList_200()
    {
        $this->factFindingAssignmentList();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->factFindingAssignment_11->columns['id'],
                    'sales' => [
                        'name' => $this->salesOne->columns['name'],
                    ],
                    'customer' => [
                        'name' => $this->customerOne->columns['name'],
                        'phone' => $this->customerOne->columns['phone'],
                    ],
                ],
                [
                    'id' => $this->factFindingAssignment_12->columns['id'],
                    'sales' => [
                        'name' => $this->salesOne->columns['name'],
                    ],
                    'customer' => [
                        'name' => $this->customerTwo->columns['name'],
                        'phone' => $this->customerTwo->columns['phone'],
                    ],
                ],
                [
                    'id' => $this->factFindingAssignment_23->columns['id'],
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
    public function test_factFindingAssignmentList_hasSalesActivityScheduleFilter()
    {
        $this->filters = [
            ['column' => 'hasSalesActivitySchedule', 'value' => false],
        ];
        $this->factFindingAssignmentList();
        $this->seeStatusCode(200);
        $this->seeJsonContains(['total' => 3]);
    }
    
    //
    protected function viewFactFindingAssignmentCount()
    {
        $this->prepareAdminDependency();
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerThree->insert($this->connection);
        
        $this->salesTwo->insert($this->connection);
        $this->salesOne->insert($this->connection);
        
        $this->customerAssignment_11->insert($this->connection);
        $this->customerAssignment_12->insert($this->connection);
        $this->customerAssignment_23->insert($this->connection);
        
        $this->factFindingAssignment_11->insert($this->connection);
        $this->factFindingAssignment_12->insert($this->connection);
        $this->factFindingAssignment_23->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ($filters: [FilterInput]) {
    viewFactFindingAssignmentCount(filters: $filters)
}
_QUERY;
        $this->graphqlVariables = $this->getPaginationInput();
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewFactFindingAssignmentCount_200()
    {
        $this->viewFactFindingAssignmentCount();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains(['viewFactFindingAssignmentCount' => 3]);
    }
    
}
