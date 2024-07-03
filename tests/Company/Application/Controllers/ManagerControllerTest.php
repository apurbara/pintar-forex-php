<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\Manager;
use Tests\Company\Application\Controllers\CompanyControllerTestCase;
use Tests\resources\Application\EntityRecord;

class ManagerControllerTest extends CompanyControllerTestCase
{
    protected EntityRecord $managerOne;
    protected EntityRecord $managerTwo;
    protected $managerPayload = [
        'name' => 'new manager name',
        'email' => 'newmanager@email.org',
        'password' => 'password123',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Manager')->truncate();
        
        $this->managerOne = new EntityRecord(Manager::class, 1);
        $this->managerTwo = new EntityRecord(Manager::class, 2);
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Manager')->truncate();
    }
    
    //
    protected function addManager()
    {
        $this->prepareAdminDependency();
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $name: String, $email: String, $password: String ){
    addManager ( name: $name, email: $email, password: $password ) {
        id, name, email
    }
}
_QUERY;
        $this->graphqlVariables = $this->managerPayload;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_addManager_200()
    {
$this->disableExceptionHandling();
        $this->addManager();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'name' => $this->managerPayload['name'],
            'email' => $this->managerPayload['email'],
        ]);
        
        $this->seeInDatabase('Manager', [
            'suspended' => false,
            'createdTime' => $this->stringOfCurrentTime(),
            'name' => $this->managerPayload['name'],
            'email' => $this->managerPayload['email'],
        ]);
    }
    
    //
    protected function suspendManager()
    {
        $this->prepareAdminDependency();
        $this->managerOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID ){
    suspendManager ( id: $id ) {
        suspended
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->managerOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_suspendManager_200()
    {
$this->disableExceptionHandling();
        $this->suspendManager();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'suspended' => true,
        ]);
        
        $this->seeInDatabase('Manager', [
            'id' => $this->managerOne->columns['id'],
            'suspended' => true,
        ]);
    }
    
    //
    protected function unsuspendManager()
    {
        $this->prepareAdminDependency();
        $this->managerOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID ){
    unsuspendManager ( id: $id ) {
        suspended
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->managerOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_unsuspendManager_200()
    {
$this->disableExceptionHandling();
        $this->managerOne->columns['suspended'] = true;
        $this->unsuspendManager();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'suspended' => false,
        ]);
        
        $this->seeInDatabase('Manager', [
            'id' => $this->managerOne->columns['id'],
            'suspended' => false,
        ]);
    }
    
    //
    protected function viewManagerList()
    {
        $this->prepareAdminDependency();
        $this->managerOne->insert($this->connection);
        $this->managerTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ManagerList {
    viewManagerList{
        list {
            id, suspended, createdTime, name, email
        },
        cursorLimit { total, cursorToNextPage }
    }
}
_QUERY;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewManagerList_200()
    {
        $this->viewManagerList();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->managerOne->columns['id'],
                    'suspended' => $this->managerOne->columns['suspended'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->managerOne->columns['createdTime']),
                    'name' => $this->managerOne->columns['name'],
                    'email' => $this->managerOne->columns['email'],
                ],
                [
                    'id' => $this->managerTwo->columns['id'],
                    'suspended' => $this->managerTwo->columns['suspended'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->managerTwo->columns['createdTime']),
                    'name' => $this->managerTwo->columns['name'],
                    'email' => $this->managerTwo->columns['email'],
                ],
            ],
            'cursorLimit' => [
                'total' => 2,
                'cursorToNextPage' => null,
            ]
        ]);
    }
    
    //
    protected function viewManagerDetail()
    {
        $this->prepareAdminDependency();
        $this->managerOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ManagerDetail ( $id: ID! ) {
    viewManagerDetail ( id: $id ) {
        id, suspended, createdTime, name, email
    }
}
_QUERY;
        $this->graphqlVariables['id'] = $this->managerOne->columns['id'];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewManagerDetail_200()
    {
        $this->viewManagerDetail();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->managerOne->columns['id'],
            'suspended' => $this->managerOne->columns['suspended'],
            'createdTime' => $this->jakartaDateTimeFormat($this->managerOne->columns['createdTime']),
            'name' => $this->managerOne->columns['name'],
            'email' => $this->managerOne->columns['email'],
        ]);
    }
}
