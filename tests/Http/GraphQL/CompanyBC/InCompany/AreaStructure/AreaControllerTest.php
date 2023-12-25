<?php

namespace App\Http\Controllers\CompanyBC\InCompany\AreaStructure;

use Company\Domain\Model\AreaStructure;
use Company\Domain\Model\AreaStructure\Area;
use Tests\Http\GraphQL\CompanyBC\CompanyBCTestCase;
use Tests\Http\Record\EntityRecord;

class AreaControllerTest extends CompanyBCTestCase
{
    protected EntityRecord $rootAreaStructure;
    protected EntityRecord $childAreaStructure;
    protected EntityRecord $areaOne;
    protected EntityRecord $areaTwo;
    protected EntityRecord $areaThree;
    
    protected $addAreaRequest = [
        'name' => "new area structure name",
        'description' => 'new area structure description',
    ];
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('AreaStructure')->truncate();
        $this->connection->table('Area')->truncate();
        
        $this->rootAreaStructure = new EntityRecord(AreaStructure::class, 'root');
        $this->childAreaStructure = new EntityRecord(AreaStructure::class, 'child');
        $this->childAreaStructure->columns['AreaStructure_idOfParent'] = $this->rootAreaStructure->columns['id'];
        
        $this->areaOne = new EntityRecord(Area::class, 1);
        $this->areaOne->columns['AreaStructure_id'] = $this->rootAreaStructure->columns['id'];
        $this->areaTwo = new EntityRecord(Area::class, 2);
        $this->areaTwo->columns['AreaStructure_id'] = $this->childAreaStructure->columns['id'];
        $this->areaTwo->columns['Area_idOfParent'] = $this->areaOne->columns['id'];
        $this->areaThree = new EntityRecord(Area::class, 3);
        $this->areaThree->columns['Area_idOfParent'] = $this->areaTwo->columns['id'];
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('AreaStructure')->truncate();
        $this->connection->table('Area')->truncate();
    }
    
    //
    protected function addRoot()
    {
        $this->prepareAdminDependency();
        $this->rootAreaStructure->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $AreaStructure_id: ID!, $name: String, $description: String ){
    addRootArea (AreaStructure_id: $AreaStructure_id, name: $name, description: $description ){
        id, disabled, createdTime, name, description,
        areaStructure { id, name }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'AreaStructure_id' => $this->rootAreaStructure->columns['id'],
            ...$this->addAreaRequest
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_addRoot_200()
    {
$this->disableExceptionHandling();
        $this->addRoot();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'name' => $this->addAreaRequest['name'],
            'description' => $this->addAreaRequest['description'],
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'areaStructure' => [
                'id' => $this->rootAreaStructure->columns['id'],
                'name' => $this->rootAreaStructure->columns['name'],
            ],
        ]);
        
        $this->seeInDatabase('Area', [
            'name' => $this->addAreaRequest['name'],
            'description' => $this->addAreaRequest['description'],
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'AreaStructure_id' => $this->rootAreaStructure->columns['id'],
        ]);
    }
    
    //
    protected function addChild()
    {
        $this->prepareAdminDependency();
        $this->rootAreaStructure->insert($this->connection);
        $this->childAreaStructure->insert($this->connection);
        $this->areaOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $Area_idOfParent: ID!, $name: String, $description: String, $AreaStructure_id: ID! ){
    addChildArea ( Area_idOfParent: $Area_idOfParent, name: $name, description: $description, AreaStructure_id: $AreaStructure_id ){
        id, disabled, createdTime, name, description,
        parent { id, name }
        areaStructure { id, name }
    }
}
_QUERY;
        $this->graphqlVariables = [
           'Area_idOfParent' => $this->areaOne->columns['id'], 
           'AreaStructure_id' => $this->childAreaStructure->columns['id'], 
            ...$this->addAreaRequest
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_addChild_200()
    {
$this->disableExceptionHandling();
        $this->addChild();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'name' => $this->addAreaRequest['name'],
            'description' => $this->addAreaRequest['description'],
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'parent' => [
                'id' => $this->areaOne->columns['id'],
                'name' => $this->areaOne->columns['name'],
            ],
            'areaStructure' => [
                'id' => $this->childAreaStructure->columns['id'],
                'name' => $this->childAreaStructure->columns['name'],
            ],
        ]);
        
        $this->seeInDatabase('Area', [
            'name' => $this->addAreaRequest['name'],
            'description' => $this->addAreaRequest['description'],
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'Area_idOfParent' => $this->areaOne->columns['id'],
            'AreaStructure_id' => $this->childAreaStructure->columns['id'],
        ]);
    }
    
    //
    protected function viewList()
    {
        $this->prepareAdminDependency();
        $this->areaOne->insert($this->connection);
        $this->areaTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query AreaList{
    viewAreaList{
        list { id, disabled, createdTime, name, description },
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
                    'id' => $this->areaOne->columns['id'],
                    'disabled' => $this->areaOne->columns['disabled'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->areaOne->columns['createdTime']),
                    'name' => $this->areaOne->columns['name'],
                    'description' => $this->areaOne->columns['description'],
                ],
                [
                    'id' => $this->areaTwo->columns['id'],
                    'disabled' => $this->areaTwo->columns['disabled'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->areaTwo->columns['createdTime']),
                    'name' => $this->areaTwo->columns['name'],
                    'description' => $this->areaTwo->columns['description'],
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
        $this->areaOne->insert($this->connection);
        $this->areaTwo->insert($this->connection);
        $this->areaThree->insert($this->connection);
        
        $this->rootAreaStructure->insert($this->connection);
        $this->childAreaStructure->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query AreaDetail ( $id: ID! ) {
    viewAreaDetail ( id: $id ) {
        id, disabled, createdTime, name, description,
        areaStructure { id, name }
        parent { id, name }
        children { list { id, name } }
    }
}
_QUERY;
        $this->graphqlVariables['id'] = $this->areaTwo->columns['id'];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewDetail_200()
    {
        $this->viewDetail();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->areaTwo->columns['id'],
            'disabled' => $this->areaTwo->columns['disabled'],
            'createdTime' => $this->jakartaDateTimeFormat($this->areaTwo->columns['createdTime']),
            'name' => $this->areaTwo->columns['name'],
            'description' => $this->areaTwo->columns['description'],
            'areaStructure' => [
                'id' => $this->childAreaStructure->columns['id'],
                'name' => $this->childAreaStructure->columns['name'],
            ],
            'parent' => [
                'id' => $this->areaOne->columns['id'],
                'name' => $this->areaOne->columns['name'],
            ],
            'children' => [
                'list' => [
                    [
                        'id' => $this->areaThree->columns['id'],
                        'name' => $this->areaThree->columns['name'],
                    ],
                ],
            ],
        ]);
    }
}
