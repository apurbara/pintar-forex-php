<?php

namespace Admin\Application\Controllers;

use Tests\Admin\Application\Controllers\AdminControllerTestCase;

class AccountControllerTest extends AdminControllerTestCase
{
    protected $editAccountPayload = [
        'name' => 'new admin name',
    ];
    protected $changePasswordPayload;
    protected function setUp(): void
    {
        parent::setUp();
        $this->changePasswordPayload = [
            'previousPassword' => $this->admin->rawPassword,
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
        $this->persistAdminDependency();
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $name: String ) {
    editAccount ( name: $name ) {
        name
    }
}
_QUERY;
        $this->graphqlVariables = $this->editAccountPayload;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_editAccount_200()
    {
        $this->editAccount();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'name' => $this->editAccountPayload['name'],
        ]);
        $this->seeInDatabase('Admin', [
            'id' => $this->admin->columns['id'],
            'name' => $this->editAccountPayload['name'],
        ]);
    }
    
    //
    protected function changePassword()
    {
        $this->persistAdminDependency();
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $previousPassword: String,  $newPassword: String ) {
    changePassword ( previousPassword: $previousPassword, newPassword: $newPassword )
}
_QUERY;
        $this->graphqlVariables = $this->changePasswordPayload;
        $this->postGraphqlRequest($this->admin->token);
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
            'email' => $this->admin->columns['email'],
            'password' => $this->changePasswordPayload['newPassword'],
        ];
        $this->postGraphqlRequest();
        $this->seeStatusCode(200);
    }
}
