<?php

namespace Manager\Application\Controllers;

use Company\Domain\Model\Province\City;
use Manager\Domain\Model\Manager\Sales;
use Tests\Http\Record\EntityRecord;
use Tests\Manager\Application\Controllers\ManagerControllerTestCase;

class SalesControllerTest extends ManagerControllerTestCase
{
    protected EntityRecord $city;
    protected EntityRecord $salesOne;
    protected EntityRecord $salesTwo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('City')->truncate();
        $this->connection->table('Sales')->truncate();
        
        $this->city = new EntityRecord(City::class, 'main');
        
        $this->salesOne = new EntityRecord(Sales::class, 1);
        $this->salesOne->columns['City_id'] = $this->city->columns['id'];
        $this->salesOne->columns['Manager_id'] = $this->manager->columns['id'];
        $this->salesTwo = new EntityRecord(Sales::class, 2);
        $this->salesTwo->columns['City_id'] = $this->city->columns['id'];
        $this->salesTwo->columns['Manager_id'] = $this->manager->columns['id'];
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('City')->truncate();
        $this->connection->table('Sales')->truncate();
    }
    
    //
    protected function viewSalesList()
    {
        $this->persistManagerDependency();
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
        $this->postGraphqlRequest($this->manager->token);
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
        $this->persistManagerDependency();
        $this->city->insert($this->connection);
        $this->salesOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query SalesDetail ( $id: ID! ) {
    viewSalesDetail ( id: $id ) {
        id, contractTerminated, createdTime, name, email,
    }
}
_QUERY;
        $this->graphqlVariables['id'] = $this->salesOne->columns['id'];
        $this->postGraphqlRequest($this->manager->token);
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
        ]);
    }
    
    //
    protected function viewAllSales()
    {
        $this->persistManagerDependency();
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
