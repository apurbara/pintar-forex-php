<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use Company\Domain\Model\AreaStructure;
use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\Personnel;
use Company\Domain\Model\Personnel\Sales;
use Tests\Http\GraphQL\CompanyBC\CompanyBCTestCase;
use Tests\Http\Record\EntityRecord;

class SalesControllerTest extends CompanyBCTestCase
{
    protected EntityRecord $personnelOne;
    protected EntityRecord $personnelTwo;
    
    protected EntityRecord $areaStructure;
    protected EntityRecord $area;
    
    protected EntityRecord $salesOne;
    protected EntityRecord $salesTwo;
    
    protected $salesAssignData = [
        'type' => 'IN_HOUSE',
    ];


    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('AreaStructure')->truncate();
        $this->connection->table('Area')->truncate();
        $this->connection->table('Personnel')->truncate();
        $this->connection->table('Sales')->truncate();
        
        $this->personnelOne = new EntityRecord(Personnel::class, 1);
        $this->personnelTwo = new EntityRecord(Personnel::class, 2);
        
        $this->areaStructure = new EntityRecord(AreaStructure::class, 'main');
        $this->area = new EntityRecord(Area::class, 'main');
        $this->area->columns['AreaStructure_id'] = $this->areaStructure->columns['id'];
        
        $this->salesOne = new EntityRecord(Sales::class, 1);
        $this->salesOne->columns['Personnel_id'] = $this->personnelOne->columns['id'];
        $this->salesOne->columns['Area_id'] = $this->area->columns['id'];
        
        $this->salesTwo = new EntityRecord(Sales::class, 2);
        $this->salesTwo->columns['Personnel_id'] = $this->personnelTwo->columns['id'];
        $this->salesTwo->columns['Area_id'] = $this->area->columns['id'];
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('AreaStructure')->truncate();
        $this->connection->table('Area')->truncate();
        $this->connection->table('Personnel')->truncate();
        $this->connection->table('Sales')->truncate();
    }
    
    //
    protected function assign()
    {
        $this->prepareAdminDependency();
        $this->personnelOne->insert($this->connection);
        $this->area->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $Personnel_id: ID!, $Area_id: ID!, $type: String ){
    assignSales ( Personnel_id: $Personnel_id, Area_id: $Area_id, type: $type ) {
        id, cancelled, createdTime, type,
        personnel { id, name }
        area { id, name }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'Personnel_id' => $this->personnelOne->columns['id'],
            'Area_id' => $this->area->columns['id'],
            ...$this->salesAssignData,
            
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_assign_200()
    {
$this->disableExceptionHandling();
        $this->assign();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'cancelled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'type' => $this->salesAssignData['type'],
            'personnel' => [
                'id' => $this->personnelOne->columns['id'],
                'name' => $this->personnelOne->columns['name'],
            ],
            'area' => [
                'id' => $this->area->columns['id'],
                'name' => $this->area->columns['name'],
            ],
        ]);
        
        $this->seeInDatabase('Sales', [
            'cancelled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'type' => $this->salesAssignData['type'],
            'Personnel_id' => $this->personnelOne->columns['id'],
            'Area_id' => $this->area->columns['id'],
        ]);
    }
    
    //
    protected function cancelSalesAssignment()
    {
        $this->prepareAdminDependency();
        $this->area->insert($this->connection);
        $this->personnelOne->insert($this->connection);
        $this->salesOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation (
    $id: ID
){
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
    public function test_cancelSalesAssignment_200()
    {
$this->disableExceptionHandling();
        $this->cancelSalesAssignment();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'cancelled' => true,
        ]);
        
        $this->seeInDatabase('Sales', [
            'id' => $this->salesOne->columns['id'],
            'cancelled' => true,
            'cancelTime' => $this->stringOfCurrentTime(),
        ]);
    }
    
    //
    protected function viewList()
    {
        $this->prepareAdminDependency();
        
        $this->personnel->insert($this->connection);
        $this->personnelOne->insert($this->connection);
        $this->personnelTwo->insert($this->connection);
        
        $this->area->insert($this->connection);
        
        $this->salesOne->insert($this->connection);
        $this->salesTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query SalesList {
    salesList{
        list {
            id, cancelled, createdTime,
            personnel { id, name }
            area { id, name }
        },
        cursorLimit { total, cursorToNextPage }
    }
}
_QUERY;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewList_200()
    {
        $this->viewList();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->salesOne->columns['id'],
                    'cancelled' => $this->salesOne->columns['cancelled'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->salesOne->columns['createdTime']),
                    'personnel' => [
                        'id' => $this->personnelOne->columns['id'],
                        'name' => $this->personnelOne->columns['name'],
                    ],
                    'area' => [
                        'id' => $this->area->columns['id'],
                        'name' => $this->area->columns['name'],
                    ],
                ],
                [
                    'id' => $this->salesTwo->columns['id'],
                    'cancelled' => $this->salesTwo->columns['cancelled'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->salesTwo->columns['createdTime']),
                    'personnel' => [
                        'id' => $this->personnelTwo->columns['id'],
                        'name' => $this->personnelTwo->columns['name'],
                    ],
                    'area' => [
                        'id' => $this->area->columns['id'],
                        'name' => $this->area->columns['name'],
                    ],
                ],
            ],
            'cursorLimit' => [
                'total' => 2,
                'cursorToNextPage' => null,
            ]
        ]);
    }
    
    //
    protected function viewDetail()
    {
        $this->prepareAdminDependency();
        $this->personnelOne->insert($this->connection);
        
        $this->area->insert($this->connection);
        
        $this->salesOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query SalesDetail ( $id: ID! ) {
    salesDetail ( id: $id ) {
        id, cancelled, createdTime,
        personnel { id, name }
        area { id, name }
    }
}
_QUERY;
        $this->graphqlVariables['id'] = $this->salesOne->columns['id'];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewDetail_200()
    {
        $this->viewDetail();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->salesOne->columns['id'],
            'cancelled' => $this->salesOne->columns['cancelled'],
            'createdTime' => $this->jakartaDateTimeFormat($this->salesOne->columns['createdTime']),
            'personnel' => [
                'id' => $this->personnelOne->columns['id'],
                'name' => $this->personnelOne->columns['name'],
            ],
            'area' => [
                'id' => $this->area->columns['id'],
                'name' => $this->area->columns['name'],
            ],
        ]);
    }
}
