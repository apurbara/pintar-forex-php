<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use Company\Domain\Model\Province;
use Tests\Http\GraphQL\CompanyBC\CompanyBCTestCase;
use Tests\Http\Record\EntityRecord;

class ProvinceControllerTest extends CompanyBCTestCase
{
    protected $provinceOne;
    protected $provinceTwo;
    protected $provincePayload = [
        'name' => 'new province name',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Province')->truncate();
        //
        $this->provinceOne = new EntityRecord(Province::class, 1);
        $this->provinceTwo = new EntityRecord(Province::class, 2);
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Province')->truncate();
    }
    
    //
    protected function addProvince()
    {
        $this->prepareAdminDependency();
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $name: String ) {
    addProvince (name: $name) {
        id, name, createdTime, disabled
    }
}
_QUERY;
        $this->graphqlVariables = $this->provincePayload;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_addProvince_200()
    {
$this->disableExceptionHandling();
        $this->addProvince();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'name' => $this->provincePayload['name'],
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
        ]);
        
        $this->seeInDatabase('Province', [
            'name' => $this->provincePayload['name'],
            'disabled' => false,
            'createdTime' => $this->stringOfCurrentTime(),
        ]);
    }
    
    //
    protected function updateProvince()
    {
        $this->prepareAdminDependency();
        $this->provinceOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( 
    $id: ID
    $name: String
) {
    updateProvince ( id: $id, name: $name ) {
        name
    }
}
_QUERY;
        $this->graphqlVariables = [
            ...$this->provincePayload,
            'id' => $this->provinceOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_updateProvince_200()
    {
$this->disableExceptionHandling();
        $this->updateProvince();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'name' => $this->provincePayload['name'],
        ]);
        
        $this->seeInDatabase('Province', [
            'id' => $this->provinceOne->columns['id'],
            'name' => $this->provincePayload['name'],
        ]);
    }
    
    //
    protected function disableProvince()
    {
        $this->prepareAdminDependency();
        $this->provinceOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( 
    $id: ID
) {
    disableProvince ( id: $id ) {
        disabled
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->provinceOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_disableProvince_200()
    {
$this->disableExceptionHandling();
        $this->disableProvince();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'disabled' => true,
        ]);
        
        $this->seeInDatabase('Province', [
            'id' => $this->provinceOne->columns['id'],
            'disabled' => true,
        ]);
    }
    
    //
    protected function enableProvince()
    {
        $this->prepareAdminDependency();
        $this->provinceOne->columns['disabled'] = true;
        $this->provinceOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( 
    $id: ID
) {
    enableProvince ( id: $id ) {
        disabled
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->provinceOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_enableProvince_200()
    {
$this->disableExceptionHandling();
        $this->enableProvince();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'disabled' => false,
        ]);
        
        $this->seeInDatabase('Province', [
            'id' => $this->provinceOne->columns['id'],
            'disabled' => false,
        ]);
    }
    
    //
    protected function viewProvinceList()
    {
        $this->prepareAdminDependency();
        $this->provinceOne->insert($this->connection);
        $this->provinceTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query {
    viewProvinceList {
        list { id, name },
        cursorLimit { total }
    }
}
_QUERY;
        $this->graphqlVariables = $this->getPaginationInput();
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewProvinceList_200()
    {
$this->disableExceptionHandling();
        $this->viewProvinceList();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->provinceOne->columns['id'],
                    'name' => $this->provinceOne->columns['name'],
                ],
                [
                    'id' => $this->provinceTwo->columns['id'],
                    'name' => $this->provinceTwo->columns['name'],
                ],
            ],
            'cursorLimit' => [ 'total' => 2 ]
        ]);
    }
    
    //
    protected function viewAllProvince()
    {
        $this->prepareAdminDependency();
        $this->provinceOne->insert($this->connection);
        $this->provinceTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query {
    viewAllProvince { id, name }
}
_QUERY;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewAllProvince_200()
    {
$this->disableExceptionHandling();
        $this->viewAllProvince();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
                'id' => $this->provinceOne->columns['id'],
                'name' => $this->provinceOne->columns['name'],
        ]);
        $this->seeJsonContains([
                'id' => $this->provinceTwo->columns['id'],
                'name' => $this->provinceTwo->columns['name'],
        ]);
    }
}
