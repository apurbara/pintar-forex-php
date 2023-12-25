<?php

namespace App\Http\Controllers\CompanyBC\InCompany\Personnel;

use Company\Domain\Model\AreaStructure;
use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\Personnel;
use Company\Domain\Model\Personnel\Manager;
use Company\Domain\Model\Personnel\Manager\Sales;
use Tests\Http\GraphQL\CompanyBC\CompanyBCTestCase;
use Tests\Http\Record\EntityRecord;

class SalesControllerTest extends CompanyBCTestCase
{
    protected EntityRecord $personnel;
    protected EntityRecord $personnelOne;
    protected EntityRecord $personnelTwo;
    
    protected EntityRecord $manager;
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
        $this->connection->table('Manager')->truncate();
        $this->connection->table('AreaStructure')->truncate();
        $this->connection->table('Area')->truncate();
        $this->connection->table('Personnel')->truncate();
        $this->connection->table('Sales')->truncate();
        
        $this->personnel = new EntityRecord(Personnel::class, 'main');
        $this->personnelOne = new EntityRecord(Personnel::class, 1);
        $this->personnelTwo = new EntityRecord(Personnel::class, 2);
        
        $this->manager = new EntityRecord(Manager::class, 'main');
        $this->manager->columns['Personnel_id'] = $this->personnel->columns['id'];
        
        $this->areaStructure = new EntityRecord(AreaStructure::class, 'main');
        $this->area = new EntityRecord(Area::class, 'main');
        $this->area->columns['AreaStructure_id'] = $this->areaStructure->columns['id'];
        
        $this->salesOne = new EntityRecord(Sales::class, 1);
        $this->salesOne->columns['Manager_id'] = $this->manager->columns['id'];
        $this->salesOne->columns['Personnel_id'] = $this->personnelOne->columns['id'];
        $this->salesOne->columns['Area_id'] = $this->area->columns['id'];
        
        $this->salesTwo = new EntityRecord(Sales::class, 2);
        $this->salesTwo->columns['Manager_id'] = $this->manager->columns['id'];
        $this->salesTwo->columns['Personnel_id'] = $this->personnelTwo->columns['id'];
        $this->salesTwo->columns['Area_id'] = $this->area->columns['id'];
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Manager')->truncate();
        $this->connection->table('AreaStructure')->truncate();
        $this->connection->table('Area')->truncate();
        $this->connection->table('Personnel')->truncate();
        $this->connection->table('Sales')->truncate();
    }
    
    //
    protected function assign()
    {
        $this->prepareAdminDependency();
        $this->personnel->insert($this->connection);
        $this->personnelOne->insert($this->connection);
        $this->manager->insert($this->connection);
        $this->area->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $Manager_id: ID!, $Personnel_id: ID!, $Area_id: ID!, $type: String){
    assignSales (Manager_id: $Manager_id, Personnel_id: $Personnel_id, Area_id: $Area_id, type: $type ) {
        id, disabled, createdTime, type,
        personnel { id, name }
        area { id, name }
        manager { id, personnel { id, name } }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'Manager_id' => $this->manager->columns['id'],
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
            'disabled' => false,
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
            'manager' => [
                'id' => $this->manager->columns['id'],
                'personnel' => [
                    'id' => $this->personnel->columns['id'],
                    'name' => $this->personnel->columns['name'],
                ],
            ],
        ]);
        
        $this->seeInDatabase('Sales', [
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'type' => $this->salesAssignData['type'],
            'Manager_id' => $this->manager->columns['id'],
            'Personnel_id' => $this->personnelOne->columns['id'],
            'Area_id' => $this->area->columns['id'],
        ]);
    }
    
    //
    protected function viewList()
    {
        $this->prepareAdminDependency();
        
        $this->personnel->insert($this->connection);
        $this->personnelOne->insert($this->connection);
        $this->personnelTwo->insert($this->connection);
        
        $this->manager->insert($this->connection);
        
        $this->area->insert($this->connection);
        
        $this->salesOne->insert($this->connection);
        $this->salesTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query SalesList {
    viewSalesList{
        list {
            id, disabled, createdTime,
            personnel { id, name }
            area { id, name }
            manager { id, personnel { id, name } }
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
                    'disabled' => $this->salesOne->columns['disabled'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->salesOne->columns['createdTime']),
                    'personnel' => [
                        'id' => $this->personnelOne->columns['id'],
                        'name' => $this->personnelOne->columns['name'],
                    ],
                    'area' => [
                        'id' => $this->area->columns['id'],
                        'name' => $this->area->columns['name'],
                    ],
                    'manager' => [
                        'id' => $this->manager->columns['id'],
                        'personnel' => [
                            'id' => $this->personnel->columns['id'],
                            'name' => $this->personnel->columns['name'],
                        ],
                    ],
                ],
                [
                    'id' => $this->salesTwo->columns['id'],
                    'disabled' => $this->salesTwo->columns['disabled'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->salesTwo->columns['createdTime']),
                    'personnel' => [
                        'id' => $this->personnelTwo->columns['id'],
                        'name' => $this->personnelTwo->columns['name'],
                    ],
                    'area' => [
                        'id' => $this->area->columns['id'],
                        'name' => $this->area->columns['name'],
                    ],
                    'manager' => [
                        'id' => $this->manager->columns['id'],
                        'personnel' => [
                            'id' => $this->personnel->columns['id'],
                            'name' => $this->personnel->columns['name'],
                        ],
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
        $this->personnel->insert($this->connection);
        $this->personnelOne->insert($this->connection);
        
        $this->manager->insert($this->connection);
        $this->area->insert($this->connection);
        
        $this->salesOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query SalesDetail ( $id: ID! ) {
    viewSalesDetail ( id: $id ) {
        id, disabled, createdTime,
        personnel { id, name }
        area { id, name }
        manager { id, personnel { id, name } }
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
            'disabled' => $this->salesOne->columns['disabled'],
            'createdTime' => $this->jakartaDateTimeFormat($this->salesOne->columns['createdTime']),
            'personnel' => [
                'id' => $this->personnelOne->columns['id'],
                'name' => $this->personnelOne->columns['name'],
            ],
            'area' => [
                'id' => $this->area->columns['id'],
                'name' => $this->area->columns['name'],
            ],
            'manager' => [
                'id' => $this->manager->columns['id'],
                'personnel' => [
                    'id' => $this->personnel->columns['id'],
                    'name' => $this->personnel->columns['name'],
                ],
            ],
        ]);
    }
}
