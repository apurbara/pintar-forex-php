<?php

namespace Sales\Application\Controllers;

use Tests\Sales\Application\Controllers\SalesControllerTestCase;

class AccountControllerTest extends SalesControllerTestCase
{
    protected $editAccountPayload = [
        'name' => 'new sales name',
    ];
    protected $changePasswordPayload;
    protected function setUp(): void
    {
        parent::setUp();
        $this->changePasswordPayload = [
            'previousPassword' => $this->sales->rawPassword,
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
        $this->prepareSalesDependency();
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $name: String ) {
    editAccount ( name: $name ) {
        name
    }
}
_QUERY;
        $this->graphqlVariables = $this->editAccountPayload;
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_editAccount_200()
    {
        $this->editAccount();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'name' => $this->editAccountPayload['name'],
        ]);
        $this->seeInDatabase('Sales', [
            'id' => $this->sales->columns['id'],
            'name' => $this->editAccountPayload['name'],
        ]);
    }
    
    //
    protected function changePassword()
    {
        $this->prepareSalesDependency();
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $previousPassword: String,  $newPassword: String ) {
    changePassword ( previousPassword: $previousPassword, newPassword: $newPassword )
}
_QUERY;
        $this->graphqlVariables = $this->changePasswordPayload;
        $this->postGraphqlRequest($this->sales->token);
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
            'email' => $this->sales->columns['email'],
            'password' => $this->changePasswordPayload['newPassword'],
        ];
        $this->postGraphqlRequest();
        $this->seeStatusCode(200);
    }
}
