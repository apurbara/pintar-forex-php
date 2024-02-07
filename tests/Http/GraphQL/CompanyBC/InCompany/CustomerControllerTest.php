<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use Company\Domain\Model\AreaStructure\Area\Customer;
use Company\Domain\Model\Personnel\Manager\Sales;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use Tests\Http\GraphQL\CompanyBC\CompanyBCTestCase;
use Tests\Http\Record\EntityRecord;

class CustomerControllerTest extends CompanyBCTestCase
{
    protected EntityRecord $customerOne;
    protected EntityRecord $customerTwo;
    //
    protected EntityRecord $salesOne;
    protected EntityRecord $customerAssignmentOne;
    //
    protected $paginationSchema = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('AssignedCustomer')->truncate();
        
        $this->customerOne = new EntityRecord(Customer::class, 1);
        $this->customerTwo = new EntityRecord(Customer::class, 2);
        //
        $this->salesOne = new EntityRecord(Sales::class, 1);
        
        $this->customerAssignmentOne = new EntityRecord(AssignedCustomer::class, 1);
        $this->customerAssignmentOne->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->customerAssignmentOne->columns['Sales_id'] = $this->salesOne->columns['id'];
        $this->customerAssignmentOne->columns['status'] = CustomerAssignmentStatus::ACTIVE->value;
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('AssignedCustomer')->truncate();
    }
    
    //
    protected function viewList()
    {
        $this->preparePersonnelDependency();
        $this->customerOne->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query CustomerList ( $filters: [FilterInput]) {
    customerList ( filters: $filters ) {
        list { id, disabled, createdTime, name, email, phone },
        cursorLimit { total, cursorToNextPage }
    }
}
_QUERY;
        $this->graphqlVariables = $this->paginationSchema;
        $this->postGraphqlRequest($this->personnel->token);
    }
    public function test_viewList_200()
    {
        $this->viewList();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->customerOne->columns['id'],
                    'disabled' => $this->customerOne->columns['disabled'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->customerOne->columns['createdTime']),
                    'name' => $this->customerOne->columns['name'],
                    'email' => $this->customerOne->columns['email'],
                    'phone' => $this->customerOne->columns['phone'],
                ],
                [
                    'id' => $this->customerTwo->columns['id'],
                    'disabled' => $this->customerTwo->columns['disabled'],
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
    public function test_viewList_appyAssignedCustomerStatusFilter()
    {
        $this->salesOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        //
        $this->paginationSchema = [
            'filters' => [
                ['column' => 'AssignedCustomer.status', 'value' => [CustomerAssignmentStatus::ACTIVE->value], 'comparisonType' => 'IN'],
            ],
        ];
        //
        $this->viewList();
        $this->seeStatusCode(200);
        $this->seeJsonContains(['id' => $this->customerOne->columns['id']]);
        $this->seeJsonDoesntContains(['id' => $this->customerTwo->columns['id']]);
        $this->seeJsonContains(['total' => 1]);
    }
    public function test_viewList_appyHasActiveAssignmentFilter()
    {
        $this->salesOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        //
        $this->paginationSchema = [
            'filters' => [
                ['column' => 'hasActiveAssignment', 'value' => true, 'comparisonType' => 'EQ'],
            ],
        ];
        //
        $this->viewList();
        $this->seeStatusCode(200);
        $this->seeJsonContains(['id' => $this->customerOne->columns['id']]);
        $this->seeJsonDoesntContains(['id' => $this->customerTwo->columns['id']]);
        $this->seeJsonContains(['total' => 1]);
    }
    
}
