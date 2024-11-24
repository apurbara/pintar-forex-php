<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\Manager;
use Company\Domain\Model\Manager\Sales;
use Company\Domain\Model\Province\City;
use Shared\Domain\Enum\SalesRole;
use Tests\Company\Application\Controllers\CompanyControllerTestCase;
use Tests\resources\Application\EntityRecord;

class SalesControllerTest extends CompanyControllerTestCase
{
    protected EntityRecord $city;
    protected EntityRecord $managerOne;
    protected EntityRecord $salesOne;
    protected EntityRecord $salesTwo;
    //
    protected $salesPayload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Manager')->truncate();
        $this->connection->table('City')->truncate();
        $this->connection->table('Sales')->truncate();
        
        $this->managerOne = new EntityRecord(Manager::class, 1);
        $this->city = new EntityRecord(City::class, 'main');
        
        $this->salesOne = new EntityRecord(Sales::class, 1);
        $this->salesOne->columns['City_id'] = $this->city->columns['id'];
        $this->salesOne->columns['Manager_id'] = $this->managerOne->columns['id'];
        $this->salesTwo = new EntityRecord(Sales::class, 2);
        $this->salesTwo->columns['City_id'] = $this->city->columns['id'];
        $this->salesTwo->columns['Manager_id'] = $this->managerOne->columns['id'];
        //
        $this->salesPayload = [
            'name' => 'new sales name',
            'email' => 'newsales@email.org',
            'password' => 'password123',
            'role' => SalesRole::STRIKER->value,
            'City_id' => $this->city->columns['id'],
            'Manager_id' => $this->managerOne->columns['id'],
        ];
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Manager')->truncate();
        $this->connection->table('City')->truncate();
        $this->connection->table('Sales')->truncate();
    }
    
    //
    protected function addSales()
    {
        $this->prepareAdminDependency();
        $this->managerOne->insert($this->connection);
        $this->city->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $role: String, $name: String, $email: String, $password: String, $City_id: ID, $Manager_id: ID ){
    addSales ( role: $role, name: $name, email: $email, password: $password, City_id: $City_id, Manager_id: $Manager_id ) {
        id, name, email,
        city { id, name }
        manager { id, name }
    }
}
_QUERY;
        $this->graphqlVariables = $this->salesPayload;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_addSales_200()
    {
$this->disableExceptionHandling();
        $this->addSales();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'name' => $this->salesPayload['name'],
            'email' => $this->salesPayload['email'],
            'city' => [
                'id' => $this->city->columns['id'],
                'name' => $this->city->columns['name'],
            ],
            'manager' => [
                'id' => $this->managerOne->columns['id'],
                'name' => $this->managerOne->columns['name'],
            ],
        ]);
        
        $this->seeInDatabase('Sales', [
            'contractTerminated' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'name' => $this->salesPayload['name'],
            'role' => $this->salesPayload['role'],
            'email' => $this->salesPayload['email'],
            'City_id' => $this->salesPayload['City_id'],
            'Manager_id' => $this->salesPayload['Manager_id'],
        ]);
    }
    
    //
    protected function updateSales()
    {
        $this->prepareAdminDependency();
        $this->managerOne->insert($this->connection);
        $this->city->insert($this->connection);
        
        $this->salesOne->columns['City_id'] = null;
        $this->salesOne->columns['Manager_id'] = $this->manager->columns['id'];
        $this->salesOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID, $role: String, $City_id: ID, $Manager_id: ID ){
    updateSales ( id: $id, role: $role, City_id: $City_id, Manager_id: $Manager_id ) {
        id, name, email, role
        city { id, name }
        manager { id, name }
    }
}
_QUERY;
        $this->graphqlVariables = [
            ...$this->salesPayload,
            'id' => $this->salesOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_updateSales_200()
    {
$this->disableExceptionHandling();
        $this->updateSales();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'role' => $this->salesPayload['role'],
            'city' => [
                'id' => $this->city->columns['id'],
                'name' => $this->city->columns['name'],
            ],
            'manager' => [
                'id' => $this->managerOne->columns['id'],
                'name' => $this->managerOne->columns['name'],
            ],
        ]);
        
        $this->seeInDatabase('Sales', [
            'role' => $this->salesPayload['role'],
            'City_id' => $this->salesPayload['City_id'],
            'Manager_id' => $this->salesPayload['Manager_id'],
        ]);
    }
    
    //
    protected function terminateSalesContract()
    {
        $this->prepareAdminDependency();
        $this->salesOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID ){
    terminateSalesContract ( id: $id ) {
        contractTerminated
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->salesOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_terminateSalesContract_200()
    {
$this->disableExceptionHandling();
        $this->terminateSalesContract();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'contractTerminated' => true,
        ]);
        
        $this->seeInDatabase('Sales', [
            'id' => $this->salesOne->columns['id'],
            'contractTerminated' => true,
        ]);
    }
    
    //
    protected function viewSalesList()
    {
        $this->prepareAdminDependency();
        $this->city->insert($this->connection);
        $this->salesOne->insert($this->connection);
        $this->salesTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query SalesList {
    viewSalesList{
        list {
            id, contractTerminated, createdTime, name, email
        },
        cursorLimit { total, cursorToNextPage }
    }
}
_QUERY;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewSalesList_200()
    {
        $this->viewSalesList();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->salesOne->columns['id'],
                    'contractTerminated' => $this->salesOne->columns['contractTerminated'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->salesOne->columns['createdTime']),
                    'name' => $this->salesOne->columns['name'],
                    'email' => $this->salesOne->columns['email'],
                ],
                [
                    'id' => $this->salesTwo->columns['id'],
                    'contractTerminated' => $this->salesTwo->columns['contractTerminated'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->salesTwo->columns['createdTime']),
                    'name' => $this->salesTwo->columns['name'],
                    'email' => $this->salesTwo->columns['email'],
                ],
            ],
            'cursorLimit' => [
                'total' => 2,
                'cursorToNextPage' => null,
            ]
        ]);
    }
    
    //
    protected function viewSalesDetail()
    {
        $this->prepareAdminDependency();
        $this->city->insert($this->connection);
        $this->salesOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query SalesDetail ( $id: ID! ) {
    viewSalesDetail ( id: $id ) {
        id, contractTerminated, createdTime, name, email,
        city { id, name }
    }
}
_QUERY;
        $this->graphqlVariables['id'] = $this->salesOne->columns['id'];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewSalesDetail_200()
    {
        $this->viewSalesDetail();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->salesOne->columns['id'],
            'contractTerminated' => $this->salesOne->columns['contractTerminated'],
            'createdTime' => $this->jakartaDateTimeFormat($this->salesOne->columns['createdTime']),
            'name' => $this->salesOne->columns['name'],
            'email' => $this->salesOne->columns['email'],
            'city' => [
                'id' => $this->city->columns['id'],
                'name' => $this->city->columns['name'],
            ],
        ]);
    }
    
    //
    protected function viewAllSales()
    {
        $this->prepareManagerDependency();
        $this->city->insert($this->connection);
        $this->salesOne->insert($this->connection);
        $this->salesTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query SalesList {
    viewAllSales {
        id, contractTerminated, createdTime, name, email
    }
}
_QUERY;
        $this->postGraphqlRequest($this->manager->token);
    }
    public function test_viewAllSales_200()
    {
        $this->viewAllSales();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->salesOne->columns['id'],
            'contractTerminated' => $this->salesOne->columns['contractTerminated'],
            'createdTime' => $this->jakartaDateTimeFormat($this->salesOne->columns['createdTime']),
            'name' => $this->salesOne->columns['name'],
            'email' => $this->salesOne->columns['email'],
        ]);
        $this->seeJsonContains([
            'id' => $this->salesTwo->columns['id'],
            'contractTerminated' => $this->salesTwo->columns['contractTerminated'],
            'createdTime' => $this->jakartaDateTimeFormat($this->salesTwo->columns['createdTime']),
            'name' => $this->salesTwo->columns['name'],
            'email' => $this->salesTwo->columns['email'],
        ]);
    }
}
