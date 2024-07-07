<?php

namespace Manager\Application\Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Shared\Application\UserRole;
use Tests\Manager\Application\Controllers\ManagerControllerTestCase;
use function env;


class LoginControllerTest extends ManagerControllerTestCase
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
        $this->persistManagerDependency();
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $email: String,  $password: String ) {
    login ( email: $email, password: $password ) {
        id, name, email, token
    }
}
_QUERY;
        $this->graphqlVariables = [
            'email' => $this->manager->columns['email'],
            'password' => $this->manager->rawPassword,
        ];
        $this->postGraphqlRequest();
    }
    public function test_login_200()
    {
        $this->login();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->manager->columns['id'],
            'name' => $this->manager->columns['name'],
            'email' => $this->manager->columns['email'],
        ]);
        
        $token = $this->response->json()['login']['token'];
        $key = env('JWT_KEY');
        $credential = JWT::decode($token, new Key($key, 'HS256'));
        $this->assertSame(UserRole::MANAGER->value, $credential->data->userRole);
        $this->assertSame($this->manager->columns['id'], $credential->data->userId);
    }
}
