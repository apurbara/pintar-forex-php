<?php

namespace Tests\Http\GraphQL\UserBC\Guest;

use Company\Domain\Model\AreaStructure\Area;
use Sales\Domain\Model\Manager\Sales;
use Tests\Http\GraphQL\GraphqlTestCase;
use Tests\Http\Record\EntityRecord;
use Tests\Http\Record\Model\AdminRecord;
use Tests\Http\Record\Model\ManagerRecord;
use Tests\Http\Record\Model\SalesRecord;
use User\Domain\Model\Manager\Manager;

class LoginControllerTest extends GraphqlTestCase
{

    protected AdminRecord $admin;
    protected ManagerRecord $manager;
    protected SalesRecord $sales;
    protected EntityRecord $area;
    
    protected $adminLoginRequest;
    protected $managerLoginRequest;
    protected $salesLoginRequest;

    protected function setUp(): void
    {
        parent::setUp();
//        $this->connection->table('Admin')->truncate();
//        $this->connection->table('Manager')->truncate();
        $this->connection->table('Area')->truncate();
        $this->connection->table('Sales')->truncate();

        $this->area = new EntityRecord(Area::class, 'main');
        
        $this->admin = new AdminRecord('main');
        $this->manager = new ManagerRecord('main');
        $this->sales = new SalesRecord('main');
        $this->sales->columns['Area_id'] = $this->area->columns['id'];
        
        $this->adminLoginRequest = [
            'email' => $this->admin->columns['email'],
            'password' => $this->admin->rawPassword,
        ];
        $this->managerLoginRequest = [
            'email' => $this->manager->columns['email'],
            'password' => $this->manager->rawPassword,
        ];
        $this->salesLoginRequest = [
            'email' => $this->sales->columns['email'],
            'password' => $this->sales->rawPassword,
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
//        $this->connection->table('Admin')->truncate();
//        $this->connection->table('Manager')->truncate();
//        $this->connection->table('Area')->truncate();
//        $this->connection->table('Sales')->truncate();
    }
    
    //
    protected function graphqlUri(): string
    {
        return 'graphql/user';
    }

    //
    protected function adminLogin()
    {
        $this->admin->insert($this->connection);
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $email: String!, $password: String! ) {
    byGuest {
        adminLogin ( email: $email, password: $password ) {
            id, aSuperUser, name, token
        }
    }
}
_QUERY;
        $this->graphqlVariables = $this->adminLoginRequest;
        $this->postGraphqlRequest();
    }

    public function test_adminLogin_200()
    {
        $this->disableExceptionHandling();
        $this->adminLogin();
        $this->seeStatusCode(200);

        $response = [
            'id' => $this->admin->columns['id'],
            'aSuperUser' => $this->admin->columns['aSuperUser'],
            'name' => $this->admin->columns['name'],
        ];
        $this->seeJsonContains($response);
//$this->seeJsonContains(['print']);
//        $this->response->dump(); //to check generated JWT token;
    }

    //
    protected function managerLogin()
    {
        $this->manager->insert($this->connection);
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $email: String!, $password: String! ) {
    byGuest {
        managerLogin ( email: $email, password: $password ) {
            id, name, token,
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            ...$this->managerLoginRequest,
            
        ];
        $this->postGraphqlRequest();
    }

    public function test_managerLogin_200()
    {
        $this->disableExceptionHandling();
        
        $this->managerLogin();
        $this->seeStatusCode(200);

        $response = [
            'id' => $this->manager->columns['id'],
            'name' => $this->manager->columns['name'],
        ];
        $this->seeJsonContains($response);
//$this->seeJsonContains(['print']);
//        $this->response->dump(); //to check generated JWT token;
    }

    //
    protected function salesLogin()
    {
        $this->sales->insert($this->connection);
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $email: String!, $password: String! ) {
    byGuest {
        salesLogin ( email: $email, password: $password ) {
            id, name, token,
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            ...$this->salesLoginRequest,
            
        ];
        $this->postGraphqlRequest();
    }

    public function test_salesLogin_200()
    {
        $this->disableExceptionHandling();
        
        $this->salesLogin();
        $this->seeStatusCode(200);

        $response = [
            'id' => $this->sales->columns['id'],
            'name' => $this->sales->columns['name'],
        ];
        $this->seeJsonContains($response);
//$this->seeJsonContains(['print']);
//        $this->response->dump(); //to check generated JWT token;
    }

}
