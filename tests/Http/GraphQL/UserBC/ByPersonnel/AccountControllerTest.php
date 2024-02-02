<?php

namespace App\Http\Controllers\UserBC\ByPersonnel;

use Tests\Http\GraphQL\UserBC\ByPersonnel\PersonnelTestCase;

class AccountControllerTest extends PersonnelTestCase
{
    protected $newName = 'new personnel name';
    protected $changePasswordInput;
    protected function setUp(): void
    {
        parent::setUp();
        $this->changePasswordInput = [
            'previousPassword' => $this->personnel->rawPassword,
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
        $this->preparePersonnelDependency();
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $name: String ) {
    byPersonnel {
        changeName ( name: $name ) {
            name
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'name' => $this->newName,
        ];
        $this->postGraphqlRequest($this->personnel->token);
    }
    public function test_changeName_200()
    {
$this->disableExceptionHandling();
        $this->changeName();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'name' => $this->newName,
        ]);
        
        $this->seeInDatabase('Personnel', [
            'id' => $this->personnel->columns['id'],
            'name' => $this->newName,
        ]);
    }
    
    //
    protected function changePassword()
    {
        $this->preparePersonnelDependency();
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $previousPassword: String, $newPassword: String ) {
    byPersonnel {
        changePassword ( previousPassword: $previousPassword, newPassword: $newPassword )
    }
}
_QUERY;
        $this->graphqlVariables = $this->changePasswordInput;
        $this->postGraphqlRequest($this->personnel->token);
    }
    public function test_changePassword_200()
    {
$this->disableExceptionHandling();
        $this->changePassword();
        $this->seeStatusCode(200);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $email: String!, $password: String!) {
    byGuest {
        personnelLogin ( email: $email, password: $password ) {
            id
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'email' => $this->personnel->columns['email'],
            'password' => $this->changePasswordInput['newPassword'],
        ];
        $this->postGraphqlRequest();
        $this->seeStatusCode(200);
        $this->seeJsonContains((['id' => $this->personnel->columns['id']]));
    }
    
    //
    protected function viewProfile()
    {
        $this->preparePersonnelDependency();
        $this->graphqlQuery = <<<'_QUERY'
query {
    byPersonnel {
        viewProfile {
            id, name
        }
    }
}
_QUERY;
        $this->postGraphqlRequest($this->personnel->token);
    }
    public function test_viewProfile_200()
    {
        $this->viewProfile();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->personnel->columns['id'],
            'name' => $this->personnel->columns['name'],
        ]);
    }
}
