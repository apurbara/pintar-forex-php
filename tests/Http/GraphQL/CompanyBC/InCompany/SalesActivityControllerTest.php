<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use Company\Domain\Model\SalesActivity;
use Tests\Http\GraphQL\CompanyBC\CompanyBCTestCase;
use Tests\Http\Record\EntityRecord;

class SalesActivityControllerTest extends CompanyBCTestCase
{
    protected EntityRecord $salesActivityOne;
    protected EntityRecord $salesActivityTwo;
    
    protected $addSalesActivityRequest = [
        'name' => "new sales activity name",
        'description' => 'new sales activity description',
        'duration' => 20,
    ];
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('SalesActivity')->truncate();
        
        $this->salesActivityOne = new EntityRecord(SalesActivity::class, 1);
        $this->salesActivityTwo = new EntityRecord(SalesActivity::class, 2);
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('SalesActivity')->truncate();
    }
    
    //
    protected function setInitial()
    {
        $this->prepareAdminDependency();
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $name: String, $description: String, $duration: Int ){
    setInitialSalesActivity(name: $name, description: $description, duration: $duration ){
        id, disabled, createdTime, name, description, duration, initial
    }
}
_QUERY;
        $this->graphqlVariables = $this->addSalesActivityRequest;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_setInitial_200()
    {
$this->disableExceptionHandling();
        $this->setInitial();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'name' => $this->addSalesActivityRequest['name'],
            'description' => $this->addSalesActivityRequest['description'],
            'duration' => $this->addSalesActivityRequest['duration'],
            'initial' => true,
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
        ]);
        
        $this->seeInDatabase('SalesActivity', [
            'name' => $this->addSalesActivityRequest['name'],
            'description' => $this->addSalesActivityRequest['description'],
            'duration' => $this->addSalesActivityRequest['duration'],
            'initial' => true,
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
        ]);
    }
    
    //
    protected function add()
    {
        $this->prepareAdminDependency();
        
        $this->graphqlQuery = <<<'_QUERY'
mutation AddSalesActivity( $name: String, $description: String, $duration: Int ){
    addSalesActivity(name: $name, description: $description, duration: $duration ){
        id, disabled, createdTime, name, description, duration, initial
    }
}
_QUERY;
        $this->graphqlVariables = $this->addSalesActivityRequest;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_add_200()
    {
$this->disableExceptionHandling();
        $this->add();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'name' => $this->addSalesActivityRequest['name'],
            'description' => $this->addSalesActivityRequest['description'],
            'duration' => $this->addSalesActivityRequest['duration'],
            'initial' => false,
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
        ]);
        
        $this->seeInDatabase('SalesActivity', [
            'name' => $this->addSalesActivityRequest['name'],
            'description' => $this->addSalesActivityRequest['description'],
            'duration' => $this->addSalesActivityRequest['duration'],
            'initial' => false,
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
        ]);
    }
    
    //
    protected function viewList()
    {
        $this->prepareAdminDependency();
        $this->salesActivityOne->insert($this->connection);
        $this->salesActivityTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query SalesActivityList{
    salesActivityList{
        list { id, disabled, createdTime, name, description, duration },
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
                    'id' => $this->salesActivityOne->columns['id'],
                    'disabled' => $this->salesActivityOne->columns['disabled'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->salesActivityOne->columns['createdTime']),
                    'name' => $this->salesActivityOne->columns['name'],
                    'description' => $this->salesActivityOne->columns['description'],
                    'duration' => $this->salesActivityOne->columns['duration'],
                ],
                [
                    'id' => $this->salesActivityTwo->columns['id'],
                    'disabled' => $this->salesActivityTwo->columns['disabled'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->salesActivityTwo->columns['createdTime']),
                    'name' => $this->salesActivityTwo->columns['name'],
                    'description' => $this->salesActivityTwo->columns['description'],
                    'duration' => $this->salesActivityTwo->columns['duration'],
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
        $this->salesActivityOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query SalesActivityDetail ( $salesActivityId: ID! ) {
    salesActivityDetail ( salesActivityId: $salesActivityId ) {
        id, disabled, createdTime, name, description, duration
    }
}
_QUERY;
        $this->graphqlVariables['salesActivityId'] = $this->salesActivityOne->columns['id'];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewDetail_200()
    {
        $this->viewDetail();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->salesActivityOne->columns['id'],
            'disabled' => $this->salesActivityOne->columns['disabled'],
            'createdTime' => $this->jakartaDateTimeFormat($this->salesActivityOne->columns['createdTime']),
            'name' => $this->salesActivityOne->columns['name'],
            'description' => $this->salesActivityOne->columns['description'],
            'duration' => $this->salesActivityOne->columns['duration'],
        ]);
    }
}
