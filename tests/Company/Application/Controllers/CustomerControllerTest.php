<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\Customer;
use Company\Domain\Model\Manager\Sales;
use Company\Domain\Model\Manager\Sales\CustomerAssignment;
use Company\Domain\Model\Manager\Sales\FactFindingAssignment;
use Company\Domain\Model\Manager\Sales\GreetingAssignment;
use Company\Domain\Model\Manager\Sales\StrikingAssignment;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Tests\Company\Application\Controllers\CompanyControllerTestCase;
use Tests\resources\Application\EntityRecord;

class CustomerControllerTest extends CompanyControllerTestCase
{
    protected EntityRecord $customerOne;
    protected EntityRecord $customerTwo;
    //
    protected EntityRecord $salesOne;
    protected EntityRecord $greetingAssignmentOne, $factFindingAssignmentOne, $strikingAssignmentOne, $customerAssignmentOne;
    //
    protected $paginationSchema = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('GreetingAssignment')->truncate();
        $this->connection->table('FactFindingAssignment')->truncate();
        $this->connection->table('StrikingAssignment')->truncate();
        
        $this->customerOne = new EntityRecord(Customer::class, 1);
        $this->customerTwo = new EntityRecord(Customer::class, 2);
        //
        $this->salesOne = new EntityRecord(Sales::class, 1);
        
        $this->customerAssignmentOne = new EntityRecord(CustomerAssignment::class, 1);
        
        $this->strikingAssignmentOne = new EntityRecord(StrikingAssignment::class, 1);
        $this->strikingAssignmentOne->columns['CustomerAssignment_id'] = $this->customerAssignmentOne->columns['id'];
        $this->strikingAssignmentOne->columns['id'] = $this->customerAssignmentOne->columns['id'];
        $this->strikingAssignmentOne->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->strikingAssignmentOne->columns['Sales_id'] = $this->salesOne->columns['id'];
        $this->strikingAssignmentOne->columns['status'] = CustomerAssignmentStatus::ACTIVE->value;
        
        $this->greetingAssignmentOne = new EntityRecord(GreetingAssignment::class, 1);
        $this->greetingAssignmentOne->columns['CustomerAssignment_id'] = $this->customerAssignmentOne->columns['id'];
        $this->greetingAssignmentOne->columns['id'] = $this->customerAssignmentOne->columns['id'];
        $this->greetingAssignmentOne->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->greetingAssignmentOne->columns['Sales_id'] = $this->salesOne->columns['id'];
        $this->greetingAssignmentOne->columns['status'] = CustomerAssignmentStatus::ACTIVE->value;
        
        $this->factFindingAssignmentOne = new EntityRecord(FactFindingAssignment::class, 1);
        $this->factFindingAssignmentOne->columns['CustomerAssignment_id'] = $this->customerAssignmentOne->columns['id'];
        $this->factFindingAssignmentOne->columns['id'] = $this->customerAssignmentOne->columns['id'];
        $this->factFindingAssignmentOne->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->factFindingAssignmentOne->columns['Sales_id'] = $this->salesOne->columns['id'];
        $this->factFindingAssignmentOne->columns['status'] = CustomerAssignmentStatus::ACTIVE->value;
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('GreetingAssignment')->truncate();
        $this->connection->table('FactFindingAssignment')->truncate();
        $this->connection->table('StrikingAssignment')->truncate();
    }
    
    //
    protected function viewList()
    {
        $this->prepareManagerDependency();
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query CustomerList ( $filters: [FilterInput]) {
    customerList ( filters: $filters ) {
        list { id, status, createdTime, name, email, phone },
        cursorLimit { total, cursorToNextPage }
    }
}
_QUERY;
        $this->graphqlVariables = $this->paginationSchema;
        $this->postGraphqlRequest($this->manager->token);
    }
    public function test_viewList_200()
    {
        $this->viewList();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->customerOne->columns['id'],
                    'status' => $this->customerOne->columns['status'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->customerOne->columns['createdTime']),
                    'name' => $this->customerOne->columns['name'],
                    'email' => $this->customerOne->columns['email'],
                    'phone' => $this->customerOne->columns['phone'],
                ],
                [
                    'id' => $this->customerTwo->columns['id'],
                    'status' => $this->customerTwo->columns['status'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->customerTwo->columns['createdTime']),
                    'name' => $this->customerTwo->columns['name'],
                    'email' => $this->customerTwo->columns['email'],
                    'phone' => $this->customerTwo->columns['phone'],
                ],
            ],
            'cursorLimit' => [
                'total' => 2,
                'cursorToNextPage' => null,
            ]
        ]);
    }
    public function test_viewList_appyHasActiveStrikingAssignmentFilter()
    {
        $this->salesOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->strikingAssignmentOne->insert($this->connection);
        //
        $this->paginationSchema = [
            'filters' => [
                ['column' => 'hasActiveStrikingAssignment', 'value' => true, 'comparisonType' => 'EQ'],
            ],
        ];
        //
        $this->viewList();
        $this->seeStatusCode(200);
        $this->seeJsonContains(['id' => $this->customerOne->columns['id']]);
        $this->seeJsonDoesntContains(['id' => $this->customerTwo->columns['id']]);
        $this->seeJsonContains(['total' => 1]);
    }
    public function test_viewList_appyHasActiveGreetingAssignmentFilter()
    {
        $this->salesOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->greetingAssignmentOne->insert($this->connection);
        //
        $this->paginationSchema = [
            'filters' => [
                ['column' => 'hasActiveGreetingAssignment', 'value' => true, 'comparisonType' => 'EQ'],
            ],
        ];
        //
        $this->viewList();
        $this->seeStatusCode(200);
        $this->seeJsonContains(['id' => $this->customerOne->columns['id']]);
        $this->seeJsonDoesntContains(['id' => $this->customerTwo->columns['id']]);
        $this->seeJsonContains(['total' => 1]);
    }
    public function test_viewList_appyHasActiveFactFindingAssignmentFilter()
    {
        $this->salesOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->factFindingAssignmentOne->insert($this->connection);
        //
        $this->paginationSchema = [
            'filters' => [
                ['column' => 'hasActiveFactFindingAssignment', 'value' => true, 'comparisonType' => 'EQ'],
            ],
        ];
        //
        $this->viewList();
        $this->seeStatusCode(200);
        $this->seeJsonContains(['id' => $this->customerOne->columns['id']]);
        $this->seeJsonDoesntContains(['id' => $this->customerTwo->columns['id']]);
        $this->seeJsonContains(['total' => 1]);
    }
    
    //
    protected function customerDetail()
    {
        $this->prepareManagerDependency();
        $this->customerOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query CustomerList ( $id: ID ) {
    customerDetail ( id: $id ) {
        id, status, name, email, phone,
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->customerOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->manager->token);
    }
    public function test_customerDetail_200()
    {
$this->disableExceptionHandling();
        $this->customerDetail();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'id' => $this->customerOne->columns['id'],
            'status' => $this->customerOne->columns['status'],
            'name' => $this->customerOne->columns['name'],
            'email' => $this->customerOne->columns['email'],
            'phone' => $this->customerOne->columns['phone'],
        ]);
    }
    
}
