<?php

namespace Manager\Application\Controllers;

use Tests\Manager\Application\Controllers\ManagerControllerTestCase;

class AccountControllerTest extends ManagerControllerTestCase
{
    protected $editAccountPayload = [
        'name' => 'new manager name',
    ];
    protected $changePasswordPayload;
    protected function setUp(): void
    {
        parent::setUp();
        $this->changePasswordPayload = [
            'previousPassword' => $this->manager->rawPassword,
            'newPassword' => 'newPassword12345',
        ];
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
    }
    
    //
    protected function editAccount()
    {
        $this->persistManagerDependency();
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $name: String ) {
    editAccount ( name: $name ) {
        name
    }
}
_QUERY;
        $this->graphqlVariables = $this->editAccountPayload;
        $this->postGraphqlRequest($this->manager->token);
    }
    public function test_editAccount_200()
    {
        $this->editAccount();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'name' => $this->editAccountPayload['name'],
        ]);
        $this->seeInDatabase('Manager', [
            'id' => $this->manager->columns['id'],
            'name' => $this->editAccountPayload['name'],
        ]);
    }
    
    //
    protected function changePassword()
    {
        $this->persistManagerDependency();
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $previousPassword: String,  $newPassword: String ) {
    changePassword ( previousPassword: $previousPassword, newPassword: $newPassword )
}
_QUERY;
        $this->graphqlVariables = $this->changePasswordPayload;
        $this->postGraphqlRequest($this->manager->token);
    }
    public function test_changePassword_200()
    {
        $this->changePassword();
        $this->seeStatusCode(200);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $email: String,  $password: String ) {
    login ( email: $email, password: $password ) {
        id
    }
}
_QUERY;
        $this->graphqlVariables = [
            'email' => $this->manager->columns['email'],
            'password' => $this->changePasswordPayload['newPassword'],
        ];
        $this->postGraphqlRequest();
        $this->seeStatusCode(200);
    }
}
