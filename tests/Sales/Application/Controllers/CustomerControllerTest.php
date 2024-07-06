<?php

namespace Sales\Application\Controllers;

use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\SalesActivity;
use DateTimeImmutable;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Tests\Http\Record\EntityRecord;
use Tests\Sales\Application\Controllers\SalesControllerTestCase;

class CustomerControllerTest extends SalesControllerTestCase
{

    protected $initialSalesActivity;
    protected $initialCustomerJourney;
    protected $registerNewCustomerRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('CustomerJourney')->truncate();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();

        $this->initialCustomerJourney = new EntityRecord(CustomerJourney::class, 'initial');
        $this->initialCustomerJourney->columns['initial'] = true;

        $this->initialSalesActivity = new EntityRecord(SalesActivity::class, 'initial');
        $this->initialSalesActivity->columns['initial'] = true;
        $this->initialSalesActivity->columns['duration'] = 30;

        $this->registerNewCustomerRequest = [
            'City_id' => $this->city->columns['id'],
            'name' => 'new customer name',
            'email' => 'newCustomer@email.org',
            'phone' => '0813213123123',
            'source' => 'wa-forex-bdg',
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
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
mutation ( $City_id: ID!, $name: String, $email: String, $phone: String, $source: String ) {
    registerNewCustomer ( City_id: $City_id, name: $name, email: $email, phone: $phone, source: $source ) {
        id, status, createdTime
        customer {
            name, email, phone, source
            city { id, name }
        }
        customerJourney { id, name, initial }
    }
}
_QUERY;
        $this->graphqlVariables = [
            ...$this->registerNewCustomerRequest
        ];
        $this->postGraphqlRequest($this->sales->token);
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
                'source' => $this->registerNewCustomerRequest['source'],
                'city' => [
                    'id' => $this->registerNewCustomerRequest['City_id'],
                    'name' => $this->city->columns['name'],
                ],
            ],
            'customerJourney' => [
                'id' => $this->initialCustomerJourney->columns['id'],
                'name' => $this->initialCustomerJourney->columns['name'],
                'initial' => true,
            ],
        ]);

        $this->seeInDatabase('CustomerAssignment', [
            'Sales_id' => $this->sales->columns['id'],
            'CustomerJourney_id' => $this->initialCustomerJourney->columns['id'],
            'status' => CustomerAssignmentStatus::ACTIVE->value,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
        ]);

        $this->seeInDatabase('Customer', [
            'City_id' => $this->city->columns['id'],
            'disabled' => false,
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'name' => $this->registerNewCustomerRequest['name'],
            'email' => $this->registerNewCustomerRequest['email'],
            'phone' => $this->registerNewCustomerRequest['phone'],
            'source' => $this->registerNewCustomerRequest['source'],
        ]);
    }

//    public function test_registerNewCustomer_200_allocateNewInitialSchedule()
//    {
//        $this->initialSalesActivity->insert($this->connection);
//        $this->registerNewCustomer();
//        
//        if ((new DateTimeImmutable())->format('w') == 5) {
//            $this->seeInDatabase('SalesActivitySchedule', [
//                'startTime' => (new DateTimeImmutable('+3 Days'))->format('Y-m-d') . " 10:00:00",
//                'SalesActivity_id' => $this->initialSalesActivity->columns['id'],
//            ]);
//        } elseif ((new DateTimeImmutable())->format('w') == 6) {
//            $this->seeInDatabase('SalesActivitySchedule', [
//                'startTime' => (new DateTimeImmutable('+2 Days'))->format('Y-m-d') . " 10:00:00",
//                'SalesActivity_id' => $this->initialSalesActivity->columns['id'],
//            ]);
//        } else {
//            $this->seeInDatabase('SalesActivitySchedule', [
//                'startTime' => (new DateTimeImmutable('+1 Days'))->format('Y-m-d') . " 10:00:00",
//                'SalesActivity_id' => $this->initialSalesActivity->columns['id'],
//            ]);
//        }
//    }
}
