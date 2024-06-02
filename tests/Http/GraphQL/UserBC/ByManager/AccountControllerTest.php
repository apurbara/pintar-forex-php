<?php

namespace App\Http\Controllers\UserBC\ByManager;

use Tests\Http\GraphQL\UserBC\ByManager\ManagerTestCase;

class AccountControllerTest extends ManagerTestCase
{
    protected $newName = 'new manager name';
    protected $changePasswordInput;
    protected function setUp(): void
    {
        parent::setUp();
        $this->changePasswordInput = [
            'previousPassword' => $this->manager->rawPassword,
            'newPassword' => 'newPassword12345',
        ];
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
    }
    
    //
    protected function changeName()
    {
        $this->prepareManagerDependency();
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $name: String ) {
    byManager {
        changeName ( name: $name ) {
            name
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'name' => $this->newName,
        ];
        $this->postGraphqlRequest($this->manager->token);
    }
    public function test_changeName_200()
    {
$this->disableExceptionHandling();
        $this->changeName();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'name' => $this->newName,
        ]);
        
        $this->seeInDatabase('Manager', [
            'id' => $this->manager->columns['id'],
            'name' => $this->newName,
        ]);
    }
    
    //
    protected function changePassword()
    {
        $this->prepareManagerDependency();
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $previousPassword: String, $newPassword: String ) {
    byManager {
        changePassword ( previousPassword: $previousPassword, newPassword: $newPassword )
    }
}
_QUERY;
        $this->graphqlVariables = $this->changePasswordInput;
        $this->postGraphqlRequest($this->manager->token);
    }
    public function test_changePassword_200()
    {
$this->disableExceptionHandling();
        $this->changePassword();
        $this->seeStatusCode(200);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $email: String!, $password: String!) {
    byGuest {
        managerLogin ( email: $email, password: $password ) {
            id
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'email' => $this->manager->columns['email'],
            'password' => $this->changePasswordInput['newPassword'],
        ];
        $this->postGraphqlRequest();
        $this->seeStatusCode(200);
        $this->seeJsonContains((['id' => $this->manager->columns['id']]));
    }
    
    //
    protected function viewProfile()
    {
        $this->prepareManagerDependency();
        $this->graphqlQuery = <<<'_QUERY'
query {
    byManager {
        viewProfile {
            id, name
        }
    }
}
_QUERY;
        $this->postGraphqlRequest($this->manager->token);
    }
    public function test_viewProfile_200()
    {
        $this->viewProfile();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->manager->columns['id'],
            'name' => $this->manager->columns['name'],
        ]);
    }
}
