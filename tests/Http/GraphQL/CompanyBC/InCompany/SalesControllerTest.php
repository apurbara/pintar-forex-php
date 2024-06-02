<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use Company\Domain\Model\Sales;
use Tests\Http\GraphQL\CompanyBC\CompanyBCTestCase;
use Tests\Http\Record\EntityRecord;

class SalesControllerTest extends CompanyBCTestCase
{
    protected EntityRecord $area;
    protected EntityRecord $salesOne;
    protected EntityRecord $salesTwo;
    //
    protected $salesPayload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Area')->truncate();
        $this->connection->table('Sales')->truncate();
        
        $this->area = new EntityRecord(\Company\Domain\Model\AreaStructure\Area::class, 'main');
        
        $this->salesOne = new EntityRecord(Sales::class, 1);
        $this->salesOne->columns['Area_id'] = $this->area->columns['id'];
        $this->salesTwo = new EntityRecord(Sales::class, 2);
        $this->salesTwo->columns['Area_id'] = $this->area->columns['id'];
        //
        $this->salesPayload = [
            'name' => 'new sales name',
            'email' => 'newsales@email.org',
            'password' => 'password123',
            'type' => \SharedContext\Domain\Enum\SalesType::IN_HOUSE->value,
            'Area_id' => $this->area->columns['id'],
        ];
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Area')->truncate();
        $this->connection->table('Sales')->truncate();
    }
    
    //
    protected function addSales()
    {
        $this->prepareAdminDependency();
        $this->area->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $name: String, $email: String, $password: String, $type: String, $Area_id: ID ){
    addSales ( name: $name, email: $email, password: $password, type: $type, Area_id: $Area_id ) {
        id, name, email, type,
        area { id, name }
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
            'type' => $this->salesPayload['type'],
            'area' => [
                'id' => $this->area->columns['id'],
                'name' => $this->area->columns['name'],
            ],
        ]);
        
        $this->seeInDatabase('Sales', [
            'cancelled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'name' => $this->salesPayload['name'],
            'email' => $this->salesPayload['email'],
            'type' => $this->salesPayload['type'],
            'Area_id' => $this->salesPayload['Area_id'],
        ]);
    }
    
    //
    protected function cancelSales()
    {
        $this->prepareAdminDependency();
        $this->salesOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID ){
    cancelSalesAssignment ( id: $id ) {
        cancelled
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->salesOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_cancelSales_200()
    {
$this->disableExceptionHandling();
        $this->cancelSales();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'cancelled' => true,
        ]);
        
        $this->seeInDatabase('Sales', [
            'id' => $this->salesOne->columns['id'],
            'cancelled' => true,
        ]);
    }
    
    //
    protected function viewSalesList()
    {
        $this->prepareAdminDependency();
        $this->area->insert($this->connection);
        $this->salesOne->insert($this->connection);
        $this->salesTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query SalesList {
    viewSalesList{
        list {
            id, cancelled, createdTime, name, email
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
                    'cancelled' => $this->salesOne->columns['cancelled'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->salesOne->columns['createdTime']),
                    'name' => $this->salesOne->columns['name'],
                    'email' => $this->salesOne->columns['email'],
                ],
                [
                    'id' => $this->salesTwo->columns['id'],
                    'cancelled' => $this->salesTwo->columns['cancelled'],
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
        $this->area->insert($this->connection);
        $this->salesOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query SalesDetail ( $id: ID! ) {
    viewSalesDetail ( id: $id ) {
        id, cancelled, createdTime, name, email,
        area { id, name }
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
            'cancelled' => $this->salesOne->columns['cancelled'],
            'createdTime' => $this->jakartaDateTimeFormat($this->salesOne->columns['createdTime']),
            'name' => $this->salesOne->columns['name'],
            'email' => $this->salesOne->columns['email'],
            'area' => [
                'id' => $this->area->columns['id'],
                'name' => $this->area->columns['name'],
            ],
        ]);
    }
}
