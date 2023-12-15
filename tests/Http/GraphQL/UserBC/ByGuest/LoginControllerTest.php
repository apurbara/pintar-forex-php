<?php

namespace Tests\Http\GraphQL\UserBC\Guest;

use Company\Domain\Model\AreaStructure\Area;
use Sales\Domain\Model\Personnel\Sales;
use Tests\Http\GraphQL\GraphqlTestCase;
use Tests\Http\Record\EntityRecord;
use Tests\Http\Record\Model\AdminRecord;
use Tests\Http\Record\Model\PersonnelRecord;
use User\Domain\Model\Personnel\Manager;

class LoginControllerTest extends GraphqlTestCase
{

    protected AdminRecord $admin;
    protected PersonnelRecord $personnel;
    protected EntityRecord $sales;
    protected EntityRecord $manager;
    protected EntityRecord $area;
    
    protected $adminLoginRequest;
    protected $personnelLoginRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Admin')->truncate();
        $this->connection->table('Personnel')->truncate();
        $this->connection->table('Area')->truncate();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('Manager')->truncate();

        $this->admin = new AdminRecord('main');
        $this->personnel = new PersonnelRecord('main');
        
        $this->area = new EntityRecord(Area::class, 'main');
        
        $this->sales = new EntityRecord(Sales::class, 'main');
        $this->sales->columns['Personnel_id'] = $this->personnel->columns['id'];
        $this->sales->columns['Area_id'] = $this->area->columns['id'];
        
        $this->manager = new EntityRecord(Manager::class, 'main');
        $this->manager->columns['Personnel_id'] = $this->personnel->columns['id'];

        $this->adminLoginRequest = [
            'email' => $this->admin->columns['email'],
            'password' => $this->admin->rawPassword,
        ];
        $this->personnelLoginRequest = [
            'email' => $this->personnel->columns['email'],
            'password' => $this->personnel->rawPassword,
        ];
    }

    protected function tearDown(): void
    {
//        parent::tearDown();
//        $this->connection->table('Admin')->truncate();
//        $this->connection->table('Personnel')->truncate();
//        $this->connection->table('Area')->truncate();
//        $this->connection->table('Sales')->truncate();
//        $this->connection->table('Manager')->truncate();
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
    protected function personnelLogin()
    {
        $this->personnel->insert($this->connection);
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $email: String!, $password: String!, $salesAssignmentFilters: [FilterInput], $managerAssignmentFilters: [FilterInput] ) {
    byGuest {
        personnelLogin ( email: $email, password: $password ) {
            id, name, token,
            salesAssignments ( filters: $salesAssignmentFilters) { list { id, disabled , area { id, name } } }
            managerAssignments ( filters: $managerAssignmentFilters) { list { id, disabled } }
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            ...$this->personnelLoginRequest,
            'salesAssignmentFilters' => [
                ['column' => 'Sales.disabled', 'value' => false],
            ],
            'managerAssignmentFilters' => [
                ['column' => 'Manager.disabled', 'value' => false],
            ],
            
        ];
        $this->postGraphqlRequest();
    }

    public function test_personnelLogin_200()
    {
        $this->disableExceptionHandling();
        
        $this->personnelLogin();
        $this->seeStatusCode(200);

        $response = [
            'id' => $this->personnel->columns['id'],
            'name' => $this->personnel->columns['name'],
        ];
        $this->seeJsonContains($response);
//$this->seeJsonContains(['print']);
//        $this->response->dump(); //to check generated JWT token;
    }
    public function test_personnelLogin_hasActiveSalesRole()
    {
        $this->area->insert($this->connection);
        $this->sales->insert($this->connection);
        $this->manager->insert($this->connection);
        
        $this->personnelLogin();
        $this->seeStatusCode(200);

        $response = [
            'id' => $this->personnel->columns['id'],
            'name' => $this->personnel->columns['name'],
            'salesAssignments' => [
                'list' => [
                    [
                        'id' => $this->sales->columns['id'],
                        'disabled' => $this->sales->columns['disabled'],
                        'area' => [
                            'id' => $this->area->columns['id'],
                            'name' => $this->area->columns['name'],
                        ],
                    ]
                ],
            ],
            'managerAssignments' => [
                'list' => [
                    [
                        'id' => $this->manager->columns['id'],
                        'disabled' => $this->manager->columns['disabled'],
                    ]
                ],
            ],
        ];
        $this->seeJsonContains($response);
//$this->seeJsonContains(['print']);
//        $this->response->dump(); //to check generated JWT token;
    }
    public function test_personnelLogin_hideDisabledSalesAssignment_200()
    {
        $this->sales->columns['disabled'] = true;
        $this->sales->insert($this->connection);
        
        $this->personnelLogin();
        $this->seeJsonDoesntContains(['id' => $this->sales->columns['id']]);
    }
    public function test_personnelLogin_hideDisabledManagerAssignment_200()
    {
        $this->manager->columns['disabled'] = true;
        $this->manager->insert($this->connection);
        
        $this->personnelLogin();
        $this->seeJsonDoesntContains(['id' => $this->manager->columns['id']]);
    }

}
