<?php

namespace Sales\Application\Controllers;

use Company\Domain\Model\Customer;
use Company\Domain\Model\Manager\Sales\CustomerAssignment;
use Company\Domain\Model\Manager\Sales\GreetingAssignment;
use Company\Domain\Model\Province\City;
use Tests\resources\Application\EntityRecord;
use Tests\Sales\Application\Controllers\SalesControllerTestCase;


class CustomerControllerTest extends SalesControllerTestCase
{
    protected EntityRecord $customer;
    protected EntityRecord $greetingAssignment, $customerAssignment;
    protected EntityRecord $cityOne;
    //
    protected $customerPayload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('City')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('GreetingAssignment')->truncate();
        //
        $this->customer = new EntityRecord(Customer::class, 'main');
        $this->customerAssignment = new EntityRecord(CustomerAssignment::class, 'main');
        $this->greetingAssignment = new EntityRecord(GreetingAssignment::class, 'main');
        $this->greetingAssignment->columns['CustomerAssignment_id'] = $this->customerAssignment->columns['id'];
        $this->greetingAssignment->columns['id'] = $this->customerAssignment->columns['id'];
        $this->greetingAssignment->columns['Customer_id'] = $this->customer->columns['id'];
        $this->greetingAssignment->columns['Sales_id'] = $this->sales->columns['id'];
        
        $this->cityOne = new EntityRecord(City::class, 'One');
        
        $this->customerPayload = [
            'City_id' => $this->cityOne->columns['id'],
            'name' => 'new customer name',
            'bio' => 'new customer bio',
            'email' => 'newAddress@email.org',
        ];
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('City')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('GreetingAssignment')->truncate();
    }
    
    //
    protected function updateCustomerRating()
    {
        $this->prepareSalesDependency();
        $this->customer->insert($this->connection);
        $this->customerAssignment->insert($this->connection);
        $this->greetingAssignment->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation (
    $customerAssignmentId: ID,
    $rating: Int,
) {
    customerAssignment ( customerAssignmentId: $customerAssignmentId ) {
        updateCustomerRating ( rating: $rating ) {
            rating
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'customerAssignmentId' => $this->greetingAssignment->columns['id'],
            'rating' => 2,
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_updateCustomerRating_200()
    {
        $this->updateCustomerRating();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'rating' => $this->graphqlVariables['rating'],
        ]);
        
        $this->seeInDatabase('Customer', [
            'id' => $this->customer->columns['id'],
            'rating' => $this->graphqlVariables['rating'],
        ]);
    }
    
    //
    protected function updateCustomer()
    {
        $this->prepareSalesDependency();

        $this->cityOne->insert($this->connection);
        $this->customer->insert($this->connection);

        $this->customerAssignment->insert($this->connection);
        $this->greetingAssignment->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
mutation ( 
    $customerAssignmentId: ID, 
    $name: String,
    $bio: String, 
    $email: String,
    $City_id: ID,
) {
    customerAssignment (
        customerAssignmentId: $customerAssignmentId,
    ) {
        updateCustomer ( 
            name: $name,
            bio: $bio, 
            email: $email,
            City_id: $City_id
        ) {
            name, bio, email, source, city { id }
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'customerAssignmentId' => $this->greetingAssignment->columns['id'],
            ...$this->customerPayload,
        ];
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_udpateCustomerBio_200()
    {
$this->disableExceptionHandling();
        $this->updateCustomer();
        $this->seeStatusCode(200);

        $this->seeJsonContains([
            'name' => $this->customerPayload['name'],
            'email' => $this->customerPayload['email'],
            'bio' => $this->customerPayload['bio'],
            'source' => $this->customer->columns['source'],
            'city' => [
                'id' => $this->customerPayload['City_id'],
            ],
        ]);

        $this->seeInDatabase('Customer', [
            'id' => $this->customer->columns['id'],
            'name' => $this->customerPayload['name'],
            'email' => $this->customerPayload['email'],
            'bio' => $this->customerPayload['bio'],
            'source' => $this->customer->columns['source'],
            'City_id' => $this->customerPayload['City_id'],
        ]);
    }
}
