<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\Province;
use Company\Domain\Model\Province\City;
use Tests\Company\Application\Controllers\CompanyControllerTestCase;
use Tests\resources\Application\EntityRecord;

class CityControllerTest extends CompanyControllerTestCase
{
    protected $provinceOne;
    protected $provinceTwo;
    protected $cityOne;
    protected $cityTwo;
    protected $cityPayload = [
        'name' => 'new city name',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Province')->truncate();
        $this->connection->table('City')->truncate();
        //
        $this->provinceOne = new EntityRecord(Province::class, 1);
        $this->provinceTwo = new EntityRecord(Province::class, 2);
        
        $this->cityOne = new EntityRecord(City::class, 1);
        $this->cityOne->columns['Province_id'] = $this->provinceOne->columns['id'];
        $this->cityTwo = new EntityRecord(City::class, 2);
        $this->cityTwo->columns['Province_id'] = $this->provinceTwo->columns['id'];
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Province')->truncate();
        $this->connection->table('City')->truncate();
    }
    
    //
    protected function addCity()
    {
        $this->prepareAdminDependency();
        $this->provinceOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $Province_id: ID, $name: String ) {
    addCity ( Province_id: $Province_id, name: $name ) {
        id, name, createdTime, disabled
        province { name }
    }
}
_QUERY;
        $this->graphqlVariables = [
            ...$this->cityPayload,
            'Province_id' => $this->provinceOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_addCity_200()
    {
$this->disableExceptionHandling();
        $this->addCity();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'name' => $this->cityPayload['name'],
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'province' => [
                'name' => $this->provinceOne->columns['name'],
            ],
        ]);
        
        $this->seeInDatabase('City', [
            'Province_id' => $this->provinceOne->columns['id'],
            'name' => $this->cityPayload['name'],
            'disabled' => false,
            'createdTime' => $this->stringOfCurrentTime(),
        ]);
    }
    
    //
    protected function updateCity()
    {
        $this->prepareAdminDependency();
        $this->provinceOne->insert($this->connection);
        $this->provinceTwo->insert($this->connection);
        $this->cityOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( 
    $id: ID
    $Province_id: ID, $name: String
) {
    updateCity ( id: $id, Province_id: $Province_id, name: $name ) {
        name,
        province { name }
    }
}
_QUERY;
        $this->graphqlVariables = [
            ...$this->cityPayload,
            'Province_id' => $this->provinceTwo->columns['id'],
            'id' => $this->cityOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_updateCity_200()
    {
$this->disableExceptionHandling();
        $this->updateCity();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'name' => $this->cityPayload['name'],
            'province' => [
                'name' => $this->provinceTwo->columns['name'],
            ],
        ]);
        
        $this->seeInDatabase('City', [
            'id' => $this->cityOne->columns['id'],
            'Province_id' => $this->provinceTwo->columns['id'],
            'name' => $this->cityPayload['name'],
        ]);
    }
    
    //
    protected function disableCity()
    {
        $this->prepareAdminDependency();
        $this->provinceOne->insert($this->connection);
        $this->cityOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( 
    $id: ID
) {
    disableCity ( id: $id ) {
        disabled
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->cityOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_disableCity_200()
    {
$this->disableExceptionHandling();
        $this->disableCity();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'disabled' => true,
        ]);
        
        $this->seeInDatabase('City', [
            'id' => $this->cityOne->columns['id'],
            'disabled' => true,
        ]);
    }
    
    //
    protected function enableCity()
    {
        $this->prepareAdminDependency();
        $this->provinceOne->columns['disabled'] = true;
        $this->cityOne->columns['disabled'] = true;
        $this->cityOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( 
    $id: ID
) {
    enableCity ( id: $id ) {
        disabled
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->cityOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_enableCity_200()
    {
$this->disableExceptionHandling();
        $this->enableCity();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'disabled' => false,
        ]);
        
        $this->seeInDatabase('City', [
            'id' => $this->cityOne->columns['id'],
            'disabled' => false,
        ]);
    }
    
    //
    protected function viewCityList()
    {
        $this->prepareAdminDependency();
        $this->provinceOne->insert($this->connection);
        $this->provinceTwo->insert($this->connection);
        $this->cityOne->insert($this->connection);
        $this->cityTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query {
    viewCityList {
        list { id, name, province { name } },
        cursorLimit { total }
    }
}
_QUERY;
        $this->graphqlVariables = $this->getPaginationInput();
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewCityList_200()
    {
$this->disableExceptionHandling();
        $this->viewCityList();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->cityOne->columns['id'],
                    'name' => $this->cityOne->columns['name'],
                    'province' => [
                        'name' => $this->provinceOne->columns['name'],
                    ],
                ],
                [
                    'id' => $this->cityTwo->columns['id'],
                    'name' => $this->cityTwo->columns['name'],
                    'province' => [
                        'name' => $this->provinceTwo->columns['name'],
                    ],
                ],
            ],
            'cursorLimit' => [ 'total' => 2 ]
        ]);
    }
    
    //
    protected function viewAllCity()
    {
        $this->prepareAdminDependency();
        $this->provinceOne->insert($this->connection);
        $this->provinceTwo->insert($this->connection);
        $this->cityOne->insert($this->connection);
        $this->cityTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query {
    viewAllCity { id, name, province { name } }
}
_QUERY;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewAllCity_200()
    {
$this->disableExceptionHandling();
        $this->viewAllCity();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->cityOne->columns['id'],
            'name' => $this->cityOne->columns['name'],
            'province' => [
                'name' => $this->provinceOne->columns['name'],
            ],
        ]);
        $this->seeJsonContains([
            'id' => $this->cityTwo->columns['id'],
            'name' => $this->cityTwo->columns['name'],
            'province' => [
                'name' => $this->provinceTwo->columns['name'],
            ],
        ]);
    }
}
