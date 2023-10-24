<?php

namespace App\Http\Controllers\CompanyBC\InCompany\Personnel;

use Company\Domain\Model\Personnel;
use Company\Domain\Model\Personnel\Manager;
use Tests\Http\GraphQL\CompanyBC\CompanyBCTestCase;
use Tests\Http\Record\EntityRecord;

class ManagerControllerTest extends CompanyBCTestCase
{
    protected EntityRecord $personnelOne;
    protected EntityRecord $personnelTwo;
    
    protected EntityRecord $managerOne;
    protected EntityRecord $managerTwo;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Personnel')->truncate();
        $this->connection->table('Manager')->truncate();
        
        $this->personnelOne = new EntityRecord(Personnel::class, 1);
        $this->personnelTwo = new EntityRecord(Personnel::class, 2);
        
        $this->managerOne = new EntityRecord(Manager::class, 1);
        $this->managerOne->columns['Personnel_id'] = $this->personnelOne->columns['id'];
        $this->managerTwo = new EntityRecord(Manager::class, 2);
        $this->managerTwo->columns['Personnel_id'] = $this->personnelTwo->columns['id'];
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Personnel')->truncate();
        $this->connection->table('Manager')->truncate();
    }
    
    //
    protected function assign()
    {
        $this->prepareAdminDependency();
        $this->personnelOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $personnelId: ID!){
    personnel ( personnelId: $personnelId ) {
        assignManager {
            id, disabled, createdTime,
            personnel { id, name }
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'personnelId' => $this->personnelOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_assign_200()
    {
$this->disableExceptionHandling();
        $this->assign();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'personnel' => [
                'id' => $this->personnelOne->columns['id'],
                'name' => $this->personnelOne->columns['name'],
            ],
        ]);
        
        $this->seeInDatabase('Manager', [
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'Personnel_id' => $this->personnelOne->columns['id'],
        ]);
    }
    
    //
    protected function viewList()
    {
        $this->prepareAdminDependency();
        $this->personnelOne->insert($this->connection);
        $this->personnelTwo->insert($this->connection);
        $this->managerOne->insert($this->connection);
        $this->managerTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ManagerList {
    managerList{
        list {
            id, disabled, createdTime,
            personnel { id, name }
        },
        cursorLimit { total, cursorToNextPage }
    }
}
_QUERY;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewList_200()
    {
        $this->viewList();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->managerOne->columns['id'],
                    'disabled' => $this->managerOne->columns['disabled'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->managerOne->columns['createdTime']),
                    'personnel' => [
                        'id' => $this->personnelOne->columns['id'],
                        'name' => $this->personnelOne->columns['name'],
                    ],
                ],
                [
                    'id' => $this->managerTwo->columns['id'],
                    'disabled' => $this->managerTwo->columns['disabled'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->managerTwo->columns['createdTime']),
                    'personnel' => [
                        'id' => $this->personnelTwo->columns['id'],
                        'name' => $this->personnelTwo->columns['name'],
                    ],
                ],
            ],
            'cursorLimit' => [
                'total' => 2,
                'cursorToNextPage' => null,
            ]
        ]);
    }
    
    //
    protected function viewDetail()
    {
        $this->prepareAdminDependency();
        $this->personnelOne->insert($this->connection);
        $this->managerOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ManagerDetail ( $managerId: ID! ) {
    managerDetail ( managerId: $managerId ) {
        id, disabled, createdTime,
        personnel { id, name }
    }
}
_QUERY;
        $this->graphqlVariables['managerId'] = $this->managerOne->columns['id'];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewDetail_200()
    {
        $this->viewDetail();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->managerOne->columns['id'],
            'disabled' => $this->managerOne->columns['disabled'],
            'createdTime' => $this->jakartaDateTimeFormat($this->managerOne->columns['createdTime']),
            'personnel' => [
                'id' => $this->personnelOne->columns['id'],
                'name' => $this->personnelOne->columns['name'],
            ],
        ]);
    }
}
