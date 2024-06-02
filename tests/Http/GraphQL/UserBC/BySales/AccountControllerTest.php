<?php

namespace App\Http\Controllers\UserBC\BySales;

use Tests\Http\GraphQL\UserBC\BySales\SalesTestCase;

class AccountControllerTest extends SalesTestCase
{
    protected $newName = 'new sales name';
    protected $changePasswordInput;
    protected function setUp(): void
    {
        parent::setUp();
        $this->changePasswordInput = [
            'previousPassword' => $this->sales->rawPassword,
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
        $this->prepareSalesDependency();
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $name: String ) {
    bySales {
        changeName ( name: $name ) {
            name
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'name' => $this->newName,
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_changeName_200()
    {
$this->disableExceptionHandling();
        $this->changeName();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'name' => $this->newName,
        ]);
        
        $this->seeInDatabase('Sales', [
            'id' => $this->sales->columns['id'],
            'name' => $this->newName,
        ]);
    }
    
    //
    protected function changePassword()
    {
        $this->prepareSalesDependency();
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $previousPassword: String, $newPassword: String ) {
    bySales {
        changePassword ( previousPassword: $previousPassword, newPassword: $newPassword )
    }
}
_QUERY;
        $this->graphqlVariables = $this->changePasswordInput;
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_changePassword_200()
    {
$this->disableExceptionHandling();
        $this->changePassword();
        $this->seeStatusCode(200);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $email: String!, $password: String!) {
    byGuest {
        salesLogin ( email: $email, password: $password ) {
            id
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'email' => $this->sales->columns['email'],
            'password' => $this->changePasswordInput['newPassword'],
        ];
        $this->postGraphqlRequest();
        $this->seeStatusCode(200);
        $this->seeJsonContains((['id' => $this->sales->columns['id']]));
    }
    
    //
    protected function viewProfile()
    {
        $this->prepareSalesDependency();
        $this->graphqlQuery = <<<'_QUERY'
query {
    bySales {
        viewProfile {
            id, name
        }
    }
}
_QUERY;
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_viewProfile_200()
    {
        $this->viewProfile();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->sales->columns['id'],
            'name' => $this->sales->columns['name'],
        ]);
    }
}
