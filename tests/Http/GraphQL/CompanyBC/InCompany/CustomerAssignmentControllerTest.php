<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use Company\Domain\Model\AreaStructure\Area\Customer;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\Sales;
use Company\Domain\Model\Sales\CustomerAssignment;
use Company\Domain\Model\SalesActivity;
use Company\Domain\Service\CustomerAssignmentDistributionServiceBuilder;
use Tests\Http\GraphQL\CompanyBC\CompanyBCTestCase;
use Tests\Http\Record\EntityRecord;

class CustomerAssignmentControllerTest extends CompanyBCTestCase
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
    
    protected $assignedMultipleCustomerToMultipleSalesInput = [];
    
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
        
        $this->customerAssignment_11 = new EntityRecord(CustomerAssignment::class, 11);
        $this->customerAssignment_11->columns['Sales_id'] = $this->salesOne->columns['id'];
        $this->customerAssignment_11->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->customerAssignment_12 = new EntityRecord(CustomerAssignment::class, 12);
        $this->customerAssignment_12->columns['Sales_id'] = $this->salesOne->columns['id'];
        $this->customerAssignment_12->columns['Customer_id'] = $this->customerTwo->columns['id'];
        $this->customerAssignment_23 = new EntityRecord(CustomerAssignment::class, 23);
        $this->customerAssignment_23->columns['Sales_id'] = $this->salesTwo->columns['id'];
        $this->customerAssignment_23->columns['Customer_id'] = $this->customerThree->columns['id'];
        
        $this->customerJourneyInitial = new EntityRecord(CustomerJourney::class, 'initial');
        $this->customerJourneyInitial->columns['initial'] = true;
        
        $this->salesActivityInitial = new EntityRecord(SalesActivity::class, 'initial');
        $this->salesActivityInitial->columns['initial'] = true;
        $this->salesActivityInitial->columns['duration'] = 20;
        
        $this->assignedMultipleCustomerToMultipleSalesInput = [
            'distributionStrategy' => CustomerAssignmentDistributionServiceBuilder::EVEN_DISTRIBUTION,
            'initiateSchedules' => false,
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
//        parent::tearDown();
//        $this->connection->table('Sales')->truncate();
//        $this->connection->table('Customer')->truncate();
//        $this->connection->table('CustomerAssignment')->truncate();
//        $this->connection->table('CustomerJourney')->truncate();
//        $this->connection->table('SalesActivity')->truncate();
//        $this->connection->table('SalesActivitySchedule')->truncate();
    }
    
    //
    protected function assignedMultipleCustomerToMultipleSales()
    {
        $this->prepareManagerDependency();
        
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerThree->insert($this->connection);
        $this->customerFour->insert($this->connection);
        
        $this->salesOne->insert($this->connection);
        $this->salesTwo->insert($this->connection);
        
        $this->customerJourneyInitial->insert($this->connection);
        $this->salesActivityInitial->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( 
    $salesList: [ID], $customerList: [ID], $distributionStrategy: String, $initiateSchedules: Boolean
) {
    assignMultipleCustomerToMultipleSales (
        salesList: $salesList, customerList: $customerList, distributionStrategy: $distributionStrategy, 
        initiateSchedules: $initiateSchedules
    )
}
_QUERY;
        $this->graphqlVariables = $this->assignedMultipleCustomerToMultipleSalesInput;
        $this->postGraphqlRequest($this->manager->token);
    }
    public function test_assignedMultipleCustomerToMultipleSales_distributeAssignment()
    {
$this->disableExceptionHandling();
        $this->assignedMultipleCustomerToMultipleSales();
        $this->seeStatusCode(200);
        
        $this->seeInDatabase('CustomerAssignment', [
            'Sales_id' => $this->salesOne->columns['id'],
            'Customer_id' => $this->customerOne->columns['id'],
            'CustomerJourney_id' => $this->customerJourneyInitial->columns['id'],
        ]);
        
        $this->seeInDatabase('CustomerAssignment', [
            'Sales_id' => $this->salesOne->columns['id'],
            'Customer_id' => $this->customerThree->columns['id'],
            'CustomerJourney_id' => $this->customerJourneyInitial->columns['id'],
        ]);
        
        $this->seeInDatabase('CustomerAssignment', [
            'Sales_id' => $this->salesTwo->columns['id'],
            'Customer_id' => $this->customerTwo->columns['id'],
            'CustomerJourney_id' => $this->customerJourneyInitial->columns['id'],
        ]);
        
        $this->seeInDatabase('CustomerAssignment', [
            'Sales_id' => $this->salesTwo->columns['id'],
            'Customer_id' => $this->customerFour->columns['id'],
            'CustomerJourney_id' => $this->customerJourneyInitial->columns['id'],
        ]);
    }
    public function test_assignedMultipleCustomerToMultipleSales_initiateSchedulesTrue_allocateInitialSalesActivity()
    {
$this->disableExceptionHandling();
        $this->assignedMultipleCustomerToMultipleSalesInput['initiateSchedules'] = true;
        $this->assignedMultipleCustomerToMultipleSalesInput['salesList'] = [$this->salesOne->columns['id']];
        $this->assignedMultipleCustomerToMultipleSales();
        $this->seeStatusCode(200);
        
        $this->seeInDatabase('SalesActivitySchedule', [
            'SalesActivity_id' => $this->salesActivityInitial->columns['id'],
        ]);
//check db manually to see if contain sales activity schedule in 11.00
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
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerThree->insert($this->connection);
        
        $this->salesTwo->insert($this->connection);
        $this->salesOne->insert($this->connection);
        
        $this->customerAssignment_11->insert($this->connection);
        $this->customerAssignment_12->insert($this->connection);
        $this->customerAssignment_23->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query {
    customerAssignmentList {
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
