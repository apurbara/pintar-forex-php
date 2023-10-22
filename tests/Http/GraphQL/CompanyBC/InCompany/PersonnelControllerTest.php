<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use Company\Domain\Model\Personnel;
use Tests\Http\GraphQL\CompanyBC\CompanyBCTestCase;
use Tests\Http\Record\EntityRecord;

class PersonnelControllerTest extends CompanyBCTestCase
{
    protected EntityRecord $personnelOne;
    protected EntityRecord $personnelTwo;
    
    protected $addPersonnelRequest = [
        'name' => "new personnel name",
        'email' => 'newPersonnel@email.org',
        'password' => 'Password123',
    ];
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Personnel')->truncate();
        
        $this->personnelOne = new EntityRecord(Personnel::class, 1);
        $this->personnelTwo = new EntityRecord(Personnel::class, 2);
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Personnel')->truncate();
    }
    
    //
    protected function add()
    {
        $this->prepareAdminDependency();
        
        $this->graphqlQuery = <<<'_QUERY'
mutation AddPersonnel( $name: String, $email: String, $password: String ){
    addPersonnel(name: $name, email: $email, password: $password){
        id, disabled, createdTime, name, email
    }
}
_QUERY;
        $this->graphqlVariables = $this->addPersonnelRequest;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_add_200()
    {
$this->disableExceptionHandling();
        $this->add();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'name' => $this->addPersonnelRequest['name'],
            'email' => $this->addPersonnelRequest['email'],
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
        ]);
        
        $this->seeInDatabase('Personnel', [
            'name' => $this->addPersonnelRequest['name'],
            'email' => $this->addPersonnelRequest['email'],
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
        ]);
    }
    
    //
    protected function viewList()
    {
        $this->prepareAdminDependency();
        $this->personnelOne->insert($this->connection);
        $this->personnelTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query PersonnelList{
    personnelList{
        list { id, disabled, createdTime, name, email },
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
                    'id' => $this->personnelOne->columns['id'],
                    'disabled' => $this->personnelOne->columns['disabled'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->personnelOne->columns['createdTime']),
                    'name' => $this->personnelOne->columns['name'],
                    'email' => $this->personnelOne->columns['email'],
                ],
                [
                    'id' => $this->personnelTwo->columns['id'],
                    'disabled' => $this->personnelTwo->columns['disabled'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->personnelTwo->columns['createdTime']),
                    'name' => $this->personnelTwo->columns['name'],
                    'email' => $this->personnelTwo->columns['email'],
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
        
        $this->graphqlQuery = <<<'_QUERY'
query PersonnelDetail ( $personnelId: ID! ) {
    personnelDetail ( personnelId: $personnelId ) {
        id, disabled, createdTime, name, email
    }
}
_QUERY;
        $this->graphqlVariables['personnelId'] = $this->personnelOne->columns['id'];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewDetail_200()
    {
        $this->viewDetail();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->personnelOne->columns['id'],
            'disabled' => $this->personnelOne->columns['disabled'],
            'createdTime' => $this->jakartaDateTimeFormat($this->personnelOne->columns['createdTime']),
            'name' => $this->personnelOne->columns['name'],
            'email' => $this->personnelOne->columns['email'],
        ]);
    }
}
