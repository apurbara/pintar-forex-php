<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use Company\Domain\Model\AreaStructure;
use Company\Domain\Model\AreaStructure\Area;
use Tests\Http\GraphQL\CompanyBC\CompanyBCTestCase;
use Tests\Http\Record\EntityRecord;

class AreaStructureControllerTest extends CompanyBCTestCase
{
    protected EntityRecord $areaStructureOne;
    protected EntityRecord $areaStructureTwo;
    protected EntityRecord $areaStructureThree;
    
    protected $areaStructurePayload = [
        'name' => "new area structure name",
        'description' => 'new area structure description',
    ];
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('AreaStructure')->truncate();
        $this->connection->table('Area')->truncate();
        
        $this->areaStructureOne = new EntityRecord(AreaStructure::class, 1);
        $this->areaStructureTwo = new EntityRecord(AreaStructure::class, 2);
        $this->areaStructureTwo->columns['AreaStructure_idOfParent'] = $this->areaStructureOne->columns['id'];
        $this->areaStructureThree = new EntityRecord(AreaStructure::class, 3);
        $this->areaStructureThree->columns['AreaStructure_idOfParent'] = $this->areaStructureTwo->columns['id'];
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
        
        $this->graphqlQuery = <<<'_QUERY'
mutation AddRootAreaStructure( $name: String, $description: String ){
    addRootAreaStructure(name: $name, description: $description ){
        id, disabled, createdTime, name, description
    }
}
_QUERY;
        $this->graphqlVariables = $this->areaStructurePayload;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_addRoot_200()
    {
$this->disableExceptionHandling();
        $this->addRoot();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'name' => $this->areaStructurePayload['name'],
            'description' => $this->areaStructurePayload['description'],
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
        ]);
        
        $this->seeInDatabase('AreaStructure', [
            'name' => $this->areaStructurePayload['name'],
            'description' => $this->areaStructurePayload['description'],
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
        ]);
    }
    
    //
    protected function addChild()
    {
        $this->prepareAdminDependency();
        $this->areaStructureOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $AreaStructure_idOfParent: ID!, $name: String, $description: String ){
    addChildAreaStructure ( AreaStructure_idOfParent: $AreaStructure_idOfParent,  name: $name, description: $description ){
        id, disabled, createdTime, name, description,
        parent { id, name }
    }
}
_QUERY;
        $this->graphqlVariables = [
           'AreaStructure_idOfParent' => $this->areaStructureOne->columns['id'], 
            ...$this->areaStructurePayload
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_addChild_200()
    {
$this->disableExceptionHandling();
        $this->addChild();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'name' => $this->areaStructurePayload['name'],
            'description' => $this->areaStructurePayload['description'],
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'parent' => [
                'id' => $this->areaStructureOne->columns['id'],
                'name' => $this->areaStructureOne->columns['name'],
            ],
        ]);
        
        $this->seeInDatabase('AreaStructure', [
            'name' => $this->areaStructurePayload['name'],
            'description' => $this->areaStructurePayload['description'],
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'AreaStructure_idOfParent' => $this->areaStructureOne->columns['id'],
        ]);
    }
    
    //
    protected function updateAreaStructure()
    {
        $this->prepareAdminDependency();
        $this->areaStructureOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID, $name: String, $description: String ){
    updateAreaStructure ( id: $id,  name: $name, description: $description ){
        id, disabled, createdTime, name, description,
        parent { id, name }
    }
}
_QUERY;
        $this->graphqlVariables = [
           'id' => $this->areaStructureOne->columns['id'], 
            ...$this->areaStructurePayload
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_updateAreaStructure_200()
    {
$this->disableExceptionHandling();
        $this->updateAreaStructure();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->areaStructureOne->columns['id'], 
            'name' => $this->areaStructurePayload['name'],
            'description' => $this->areaStructurePayload['description'],
            'disabled' => false,
        ]);
        
        $this->seeInDatabase('AreaStructure', [
            'id' => $this->areaStructureOne->columns['id'], 
            'name' => $this->areaStructurePayload['name'],
            'description' => $this->areaStructurePayload['description'],
        ]);
    }
    
    //
    protected function disableAreaStructure()
    {
        $this->prepareAdminDependency();
        $this->areaStructureOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID ){
    disableAreaStructure ( id: $id ){
        id, disabled 
    }
}
_QUERY;
        $this->graphqlVariables = [
           'id' => $this->areaStructureOne->columns['id'], 
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_disableAreaStructure_200()
    {
$this->disableExceptionHandling();
        $this->disableAreaStructure();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->areaStructureOne->columns['id'], 
            'disabled' => true,
        ]);
        
        $this->seeInDatabase('AreaStructure', [
            'id' => $this->areaStructureOne->columns['id'], 
            'disabled' => true,
        ]);
    }
    public function test_disable_hasActiveChildren_403()
    {
        $this->areaStructureTwo->insert($this->connection);
        $this->disableAreaStructure();
        $this->seeStatusCode(403);
    }
    public function test_disable_hasNoActiveChildren_200()
    {
        $this->areaStructureTwo->columns['disabled'] = true;
        $this->areaStructureTwo->insert($this->connection);
        $this->disableAreaStructure();
        $this->seeStatusCode(200);
    }
    public function test_disable_hasActiveArea_403()
    {
        $area = new EntityRecord(Area::class, 'area');
        $area->columns['AreaStructure_id'] = $this->areaStructureOne->columns['id'];
        $area->insert($this->connection);
        $this->disableAreaStructure();
        $this->seeStatusCode(403);
    }
    public function test_disable_noActiveArea_200()
    {
        $area = new EntityRecord(Area::class, 'area');
        $area->columns['AreaStructure_id'] = $this->areaStructureOne->columns['id'];
        $area->columns['disabled'] = true;
        $area->insert($this->connection);
        $this->disableAreaStructure();
        $this->seeStatusCode(200);
    }
    
    //
    protected function viewList()
    {
        $this->prepareAdminDependency();
        $this->areaStructureOne->insert($this->connection);
        $this->areaStructureTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query {
    areaStructureList{
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
                    'id' => $this->areaStructureOne->columns['id'],
                    'disabled' => $this->areaStructureOne->columns['disabled'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->areaStructureOne->columns['createdTime']),
                    'name' => $this->areaStructureOne->columns['name'],
                    'description' => $this->areaStructureOne->columns['description'],
                ],
                [
                    'id' => $this->areaStructureTwo->columns['id'],
                    'disabled' => $this->areaStructureTwo->columns['disabled'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->areaStructureTwo->columns['createdTime']),
                    'name' => $this->areaStructureTwo->columns['name'],
                    'description' => $this->areaStructureTwo->columns['description'],
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
        $this->areaStructureOne->insert($this->connection);
        $this->areaStructureTwo->insert($this->connection);
        $this->areaStructureThree->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $id: ID! ) {
    areaStructureDetail ( id: $id ) {
        id, disabled, createdTime, name, description, 
        parent { id, name },
        children { list { id, name } }
    }
}
_QUERY;
        $this->graphqlVariables['id'] = $this->areaStructureTwo->columns['id'];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewDetail_200()
    {
        $this->viewDetail();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->areaStructureTwo->columns['id'],
            'disabled' => $this->areaStructureTwo->columns['disabled'],
            'createdTime' => $this->jakartaDateTimeFormat($this->areaStructureTwo->columns['createdTime']),
            'name' => $this->areaStructureTwo->columns['name'],
            'description' => $this->areaStructureTwo->columns['description'],
            'parent' => [
                'id' => $this->areaStructureOne->columns['id'],
                'name' => $this->areaStructureOne->columns['name'],
            ],
            'children' => [
                'list' => [
                    [
                        'id' => $this->areaStructureThree->columns['id'],
                        'name' => $this->areaStructureThree->columns['name'],
                    ],
                ],
            ],
        ]);
    }
}
