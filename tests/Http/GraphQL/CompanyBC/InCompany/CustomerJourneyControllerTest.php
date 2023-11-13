<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use Company\Domain\Model\CustomerJourney;
use Tests\Http\GraphQL\CompanyBC\CompanyBCTestCase;
use Tests\Http\Record\EntityRecord;

class CustomerJourneyControllerTest extends CompanyBCTestCase
{
    protected EntityRecord $customerJourneyOne;
    protected EntityRecord $customerJourneyTwo;
    
    protected $addCustomerJourneyRequest = [
        'name' => "new sales activity name",
        'description' => 'new sales activity description',
    ];
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('CustomerJourney')->truncate();
        
        $this->customerJourneyOne = new EntityRecord(CustomerJourney::class, 1);
        $this->customerJourneyTwo = new EntityRecord(CustomerJourney::class, 2);
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('CustomerJourney')->truncate();
    }
    
    //
    protected function setInitial()
    {
        $this->prepareAdminDependency();
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $name: String, $description: String ){
    setInitialCustomerJourney(name: $name, description: $description ){
        id, disabled, createdTime, name, description, initial
    }
}
_QUERY;
        $this->graphqlVariables = $this->addCustomerJourneyRequest;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_setInitial_200()
    {
$this->disableExceptionHandling();
        $this->setInitial();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'name' => $this->addCustomerJourneyRequest['name'],
            'description' => $this->addCustomerJourneyRequest['description'],
            'initial' => true,
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
        ]);
        
        $this->seeInDatabase('CustomerJourney', [
            'name' => $this->addCustomerJourneyRequest['name'],
            'description' => $this->addCustomerJourneyRequest['description'],
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
mutation AddCustomerJourney( $name: String, $description: String ){
    addCustomerJourney(name: $name, description: $description ){
        id, disabled, createdTime, name, description, initial
    }
}
_QUERY;
        $this->graphqlVariables = $this->addCustomerJourneyRequest;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_add_200()
    {
$this->disableExceptionHandling();
        $this->add();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'name' => $this->addCustomerJourneyRequest['name'],
            'description' => $this->addCustomerJourneyRequest['description'],
            'initial' => false,
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
        ]);
        
        $this->seeInDatabase('CustomerJourney', [
            'name' => $this->addCustomerJourneyRequest['name'],
            'description' => $this->addCustomerJourneyRequest['description'],
            'initial' => false,
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
        ]);
    }
    
    //
    protected function viewList()
    {
        $this->prepareAdminDependency();
        $this->customerJourneyOne->insert($this->connection);
        $this->customerJourneyTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query CustomerJourneyList{
    customerJourneyList{
        list { id, disabled, createdTime, name, description },
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
                    'id' => $this->customerJourneyOne->columns['id'],
                    'disabled' => $this->customerJourneyOne->columns['disabled'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->customerJourneyOne->columns['createdTime']),
                    'name' => $this->customerJourneyOne->columns['name'],
                    'description' => $this->customerJourneyOne->columns['description'],
                ],
                [
                    'id' => $this->customerJourneyTwo->columns['id'],
                    'disabled' => $this->customerJourneyTwo->columns['disabled'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->customerJourneyTwo->columns['createdTime']),
                    'name' => $this->customerJourneyTwo->columns['name'],
                    'description' => $this->customerJourneyTwo->columns['description'],
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
        $this->customerJourneyOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query CustomerJourneyDetail ( $customerJourneyId: ID! ) {
    customerJourneyDetail ( customerJourneyId: $customerJourneyId ) {
        id, disabled, createdTime, name, description
    }
}
_QUERY;
        $this->graphqlVariables['customerJourneyId'] = $this->customerJourneyOne->columns['id'];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewDetail_200()
    {
        $this->viewDetail();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->customerJourneyOne->columns['id'],
            'disabled' => $this->customerJourneyOne->columns['disabled'],
            'createdTime' => $this->jakartaDateTimeFormat($this->customerJourneyOne->columns['createdTime']),
            'name' => $this->customerJourneyOne->columns['name'],
            'description' => $this->customerJourneyOne->columns['description'],
        ]);
    }
}
