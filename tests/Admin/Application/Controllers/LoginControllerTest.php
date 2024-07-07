<?php

namespace Admin\Application\Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Shared\Application\UserRole;
use Tests\Admin\Application\Controllers\AdminControllerTestCase;
use function env;

class LoginControllerTest extends AdminControllerTestCase
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
        $this->persistAdminDependency();
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $email: String,  $password: String ) {
    login ( email: $email, password: $password ) {
        id, name, email, token
    }
}
_QUERY;
        $this->graphqlVariables = [
            'email' => $this->admin->columns['email'],
            'password' => $this->admin->rawPassword,
        ];
        $this->postGraphqlRequest();
    }
    public function test_login_200()
    {
        $this->login();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->admin->columns['id'],
            'name' => $this->admin->columns['name'],
            'email' => $this->admin->columns['email'],
        ]);
        
        $token = $this->response->json()['login']['token'];
        $key = env('JWT_KEY');
        $credential = JWT::decode($token, new Key($key, 'HS256'));
        $this->assertSame(UserRole::ADMIN->value, $credential->data->userRole);
        $this->assertSame($this->admin->columns['id'], $credential->data->userId);
    }
}
