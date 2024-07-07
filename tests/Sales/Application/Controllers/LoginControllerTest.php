<?php

namespace Sales\Application\Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Shared\Application\UserRole;
use Tests\Sales\Application\Controllers\SalesControllerTestCase;
use function env;

class LoginControllerTest extends SalesControllerTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
    
    //
    protected function login()
    {
        $this->prepareSalesDependency();
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $email: String,  $password: String ) {
    login ( email: $email, password: $password ) {
        id, name, email, token
    }
}
_QUERY;
        $this->graphqlVariables = [
            'email' => $this->sales->columns['email'],
            'password' => $this->sales->rawPassword,
        ];
        $this->postGraphqlRequest();
    }
    public function test_login_200()
    {
        $this->login();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->sales->columns['id'],
            'name' => $this->sales->columns['name'],
            'email' => $this->sales->columns['email'],
        ]);
        
        $token = $this->response->json()['login']['token'];
        $key = env('JWT_KEY');
        $credential = JWT::decode($token, new Key($key, 'HS256'));
        $this->assertSame(UserRole::SALES->value, $credential->data->userRole);
        $this->assertSame($this->sales->columns['id'], $credential->data->userId);
    }
}
