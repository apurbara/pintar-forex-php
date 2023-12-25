<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use Company\Domain\Model\CustomerVerification;
use Tests\Http\GraphQL\CompanyBC\CompanyBCTestCase;
use Tests\Http\Record\EntityRecord;

class CustomerVerificationControllerTest extends CompanyBCTestCase
{
    protected EntityRecord $customerVerificationOne;
    protected EntityRecord $customerVerificationTwo;
    
    protected $addCustomerVerificationRequest = [
        'name' => "new customer verification name",
        'description' => 'new customer verification description',
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
mutation ( $name: String, $description: String){
    addCustomerVerification(name: $name, description: $description ){
        id, disabled, createdTime, name, description
    }
}
_QUERY;
        $this->graphqlVariables = $this->addCustomerVerificationRequest;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_add_200()
    {
$this->disableExceptionHandling();
        $this->add();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'name' => $this->addCustomerVerificationRequest['name'],
            'description' => $this->addCustomerVerificationRequest['description'],
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
        ]);
        
        $this->seeInDatabase('CustomerVerification', [
            'name' => $this->addCustomerVerificationRequest['name'],
            'description' => $this->addCustomerVerificationRequest['description'],
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
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
    viewCustomerVerificationList{
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
    viewCustomerVerificationDetail ( id: $id ) {
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
