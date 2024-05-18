<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use Company\Domain\Model\CustomerVerification;
use Tests\Http\GraphQL\CompanyBC\CompanyBCTestCase;
use Tests\Http\Record\EntityRecord;

class CustomerVerificationControllerTest extends CompanyBCTestCase
{
    protected EntityRecord $customerVerificationOne;
    protected EntityRecord $customerVerificationTwo;
    
    protected $customerVerificationPayload = [
        'name' => "new customer verification name",
        'description' => 'new customer verification description',
        'weight' => 10,
        'position' => 3,
    ];
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('CustomerVerification')->truncate();
        
        $this->customerVerificationOne = new EntityRecord(CustomerVerification::class, 1);
        $this->customerVerificationTwo = new EntityRecord(CustomerVerification::class, 2);
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('CustomerVerification')->truncate();
    }
    
    //
    protected function add()
    {
        $this->prepareAdminDependency();
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $name: String, $description: String, $weight: Int, $position: Int){
    addCustomerVerification(name: $name, description: $description, weight: $weight, position: $position ){
        id, disabled, createdTime, name, description, weight, position
    }
}
_QUERY;
        $this->graphqlVariables = $this->customerVerificationPayload;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_add_200()
    {
$this->disableExceptionHandling();
        $this->add();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'name' => $this->customerVerificationPayload['name'],
            'description' => $this->customerVerificationPayload['description'],
            'weight' => $this->customerVerificationPayload['weight'],
            'position' => $this->customerVerificationPayload['position'],
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
        ]);
        
        $this->seeInDatabase('CustomerVerification', [
            'name' => $this->customerVerificationPayload['name'],
            'description' => $this->customerVerificationPayload['description'],
            'weight' => $this->customerVerificationPayload['weight'],
            'position' => $this->customerVerificationPayload['position'],
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
        ]);
    }
    
    //
    protected function updateCustomerVerification()
    {
        $this->prepareAdminDependency();
        $this->customerVerificationOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( 
    $id: ID, 
    $name: String, $description: String, $weight: Int, $position: Int
){
    updateCustomerVerification(
        id: $id, 
        name: $name, description: $description, weight: $weight, position: $position
    ){
        id, name, description, weight, position
    }
}
_QUERY;
        $this->graphqlVariables = [
            ...$this->customerVerificationPayload,
            'id' => $this->customerVerificationOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_updateCustomerVerification_200()
    {
$this->disableExceptionHandling();
        $this->updateCustomerVerification();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->customerVerificationOne->columns['id'],
            'name' => $this->customerVerificationPayload['name'],
            'description' => $this->customerVerificationPayload['description'],
            'weight' => $this->customerVerificationPayload['weight'],
            'position' => $this->customerVerificationPayload['position'],
        ]);
        
        $this->seeInDatabase('CustomerVerification', [
            'id' => $this->customerVerificationOne->columns['id'],
            'name' => $this->customerVerificationPayload['name'],
            'description' => $this->customerVerificationPayload['description'],
            'weight' => $this->customerVerificationPayload['weight'],
            'position' => $this->customerVerificationPayload['position'],
        ]);
    }
    
    //
    protected function disableCustomerVerification()
    {
        $this->prepareAdminDependency();
        $this->customerVerificationOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( 
    $id: ID, 
){
    disableCustomerVerification(
        id: $id, 
    ){
        id, disabled
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->customerVerificationOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_disableCustomerVerification_200()
    {
$this->disableExceptionHandling();
        $this->disableCustomerVerification();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->customerVerificationOne->columns['id'],
            'disabled' => true,
        ]);
        
        $this->seeInDatabase('CustomerVerification', [
            'id' => $this->customerVerificationOne->columns['id'],
            'disabled' => true,
        ]);
    }
    
    //
    protected function enableCustomerVerification()
    {
        $this->prepareAdminDependency();
        $this->customerVerificationOne->columns['disabled'] = true;
        $this->customerVerificationOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( 
    $id: ID, 
){
    enableCustomerVerification(
        id: $id, 
    ){
        id, disabled
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->customerVerificationOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_enableCustomerVerification_200()
    {
$this->disableExceptionHandling();
        $this->enableCustomerVerification();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->customerVerificationOne->columns['id'],
            'disabled' => false,
        ]);
        
        $this->seeInDatabase('CustomerVerification', [
            'id' => $this->customerVerificationOne->columns['id'],
            'disabled' => false,
        ]);
    }
    
    //
    protected function viewList()
    {
        $this->prepareAdminDependency();
        $this->customerVerificationOne->insert($this->connection);
        $this->customerVerificationTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query {
    customerVerificationList{
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
                    'id' => $this->customerVerificationOne->columns['id'],
                    'disabled' => $this->customerVerificationOne->columns['disabled'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->customerVerificationOne->columns['createdTime']),
                    'name' => $this->customerVerificationOne->columns['name'],
                    'description' => $this->customerVerificationOne->columns['description'],
                ],
                [
                    'id' => $this->customerVerificationTwo->columns['id'],
                    'disabled' => $this->customerVerificationTwo->columns['disabled'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->customerVerificationTwo->columns['createdTime']),
                    'name' => $this->customerVerificationTwo->columns['name'],
                    'description' => $this->customerVerificationTwo->columns['description'],
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
        $this->customerVerificationOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query CustomerVerificationDetail ( $id: ID! ) {
    customerVerificationDetail ( id: $id ) {
        id, disabled, createdTime, name, description
    }
}
_QUERY;
        $this->graphqlVariables['id'] = $this->customerVerificationOne->columns['id'];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewDetail_200()
    {
        $this->viewDetail();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->customerVerificationOne->columns['id'],
            'disabled' => $this->customerVerificationOne->columns['disabled'],
            'createdTime' => $this->jakartaDateTimeFormat($this->customerVerificationOne->columns['createdTime']),
            'name' => $this->customerVerificationOne->columns['name'],
            'description' => $this->customerVerificationOne->columns['description'],
        ]);
    }
}
