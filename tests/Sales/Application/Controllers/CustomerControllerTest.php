<?php

namespace Sales\Application\Controllers;

use Company\Domain\Model\Customer;
use Company\Domain\Model\Manager\Sales\CustomerAssignment;
use Company\Domain\Model\Manager\Sales\GreetingAssignment;
use Tests\resources\Application\EntityRecord;
use Tests\Sales\Application\Controllers\SalesControllerTestCase;


class CustomerControllerTest extends SalesControllerTestCase
{
    protected EntityRecord $customer;
    protected EntityRecord $greetingAssignment, $customerAssignment;
    
    protected function setUp(): void
    {
        parent::setUp();
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
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
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
    $CustomerAssignment_id: ID,
    $rating: Int,
) {
    updateCustomerRating (
        CustomerAssignment_id: $CustomerAssignment_id,
        rating: $rating,
    ) {
        rating
    }
}
_QUERY;
        $this->graphqlVariables = [
            'CustomerAssignment_id' => $this->greetingAssignment->columns['id'],
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
}
