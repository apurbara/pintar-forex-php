<?php

namespace App\Http\Controllers\SalesBC;

use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\SalesActivity;
use DateTimeImmutable;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use Tests\Http\GraphQL\SalesBC\SalesBCTestCase;
use Tests\Http\Record\EntityRecord;

class CustomerControllerTest extends SalesBCTestCase
{

    protected $initialSalesActivity;
    protected $initialCustomerJourney;
    protected $registerNewCustomerRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('AssignedCustomer')->truncate();
        $this->connection->table('CustomerJourney')->truncate();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();

        $this->initialCustomerJourney = new EntityRecord(CustomerJourney::class, 'initial');
        $this->initialCustomerJourney->columns['initial'] = true;

        $this->initialSalesActivity = new EntityRecord(SalesActivity::class, 'initial');
        $this->initialSalesActivity->columns['initial'] = true;
        $this->initialSalesActivity->columns['duration'] = 30;

        $this->registerNewCustomerRequest = [
            'Area_id' => $this->area->columns['id'],
            'name' => 'new customer name',
            'email' => 'newCustomer@email.org',
            'phone' => '0813213123123',
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('AssignedCustomer')->truncate();
        $this->connection->table('CustomerJourney')->truncate();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
    }

    //  
    protected function registerNewCustomer()
    {
$this->disableExceptionHandling();
        $this->prepareSalesDependency();
        $this->initialCustomerJourney->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
mutation ( $salesId: ID!, $Area_id: ID!, $name: String, $email: String, $phone: String ) {
    sales ( salesId: $salesId ) {
        registerNewCustomer ( Area_id: $Area_id, name: $name, email: $email, phone: $phone ) {
            id, status, createdTime
            customer {
                name, email, phone
                area { id, name }
            }
            customerJourney { id, name, initial }
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'salesId' => $this->sales->columns['id'],
            ...$this->registerNewCustomerRequest
        ];
        $this->postGraphqlRequest($this->personnel->token);
    }

    public function test_registerNewCustomer_200()
    {
        $this->registerNewCustomer();
        $this->seeStatusCode(200);

        $this->seeJsonContains([
            'status' => CustomerAssignmentStatus::ACTIVE->value,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'customer' => [
                'name' => $this->registerNewCustomerRequest['name'],
                'email' => $this->registerNewCustomerRequest['email'],
                'phone' => $this->registerNewCustomerRequest['phone'],
                'area' => [
                    'id' => $this->registerNewCustomerRequest['Area_id'],
                    'name' => $this->area->columns['name'],
                ],
            ],
            'customerJourney' => [
                'id' => $this->initialCustomerJourney->columns['id'],
                'name' => $this->initialCustomerJourney->columns['name'],
                'initial' => true,
            ],
        ]);

        $this->seeInDatabase('AssignedCustomer',
                [
            'Sales_id' => $this->sales->columns['id'],
            'CustomerJourney_id' => $this->initialCustomerJourney->columns['id'],
            'status' => CustomerAssignmentStatus::ACTIVE->value,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
        ]);

        $this->seeInDatabase('Customer',
                [
            'Area_id' => $this->area->columns['id'],
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'name' => $this->registerNewCustomerRequest['name'],
            'email' => $this->registerNewCustomerRequest['email'],
            'phone' => $this->registerNewCustomerRequest['phone'],
        ]);
    }

    public function test_registerNewCustomer_200_allocateNewInitialSchedule()
    {
        $this->initialSalesActivity->insert($this->connection);
        $this->registerNewCustomer();
        
        if ((new DateTimeImmutable())->format('w') == 5) {
            $this->seeInDatabase('SalesActivitySchedule', [
                'startTime' => (new DateTimeImmutable('+3 Days'))->format('Y-m-d') . " 10:00:00",
                'SalesActivity_id' => $this->initialSalesActivity->columns['id'],
            ]);
        } elseif ((new DateTimeImmutable())->format('w') == 6) {
            $this->seeInDatabase('SalesActivitySchedule', [
                'startTime' => (new DateTimeImmutable('+2 Days'))->format('Y-m-d') . " 10:00:00",
                'SalesActivity_id' => $this->initialSalesActivity->columns['id'],
            ]);
        } else {
            $this->seeInDatabase('SalesActivitySchedule', [
                'startTime' => (new DateTimeImmutable('+1 Days'))->format('Y-m-d') . " 10:00:00",
                'SalesActivity_id' => $this->initialSalesActivity->columns['id'],
            ]);
        }
    }
}
