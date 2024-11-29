<?php

namespace Sales\Application\Controllers;

use Company\Domain\Model\SalesActivity;
use DateTime;
use Sales\Domain\DependencyModel\Customer;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Sales\Domain\Model\Sales\FactFindingAssignment;
use Sales\Domain\Model\Sales\GreetingAssignment;
use Sales\Domain\Model\Sales\StrikingAssignment;
use Shared\Domain\Enum\SalesActivityScheduleStatus;
use Shared\Domain\Enum\SalesRole;
use Tests\Http\Record\EntityRecord;
use Tests\Sales\Application\Controllers\SalesControllerTestCase;

class SalesActivityReportControllerTest extends SalesControllerTestCase
{
    protected $salesActivity;
    
    protected $customer;
    
    protected $greetingAssignment, $factFindingAssignment, $strikingAssignment, $customerAssignment;
    
    protected $salesActivitySchedule;
    
    protected $salesActivityReportOne;
    protected $salesActivityReportTwo;
    
    protected $submitNonScheduledSalesActivityReportPayload;
    protected $submitSalesActivityReportPayload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('SalesActivity')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('GreetingAssignment')->truncate();
        $this->connection->table('FactFindingAssignment')->truncate();
        $this->connection->table('StrikingAssignment')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
        $this->connection->table('SalesActivityReport')->truncate();
        
        $this->salesActivity = new EntityRecord(SalesActivity::class, 'main');
        
        $this->customer = new EntityRecord(Customer::class, 'main');
        
        $this->customerAssignment = new EntityRecord(CustomerAssignment::class, 'main');
        
        $this->greetingAssignment = new EntityRecord(GreetingAssignment::class, 'main');
        $this->greetingAssignment->columns['CustomerAssignment_id'] = $this->customerAssignment->columns['id'];
        $this->greetingAssignment->columns['id'] = $this->customerAssignment->columns['id'];
        $this->greetingAssignment->columns['Customer_id'] = $this->customer->columns['id'];
        $this->greetingAssignment->columns['Sales_id'] = $this->sales->columns['id'];
        
        $this->factFindingAssignment = new EntityRecord(FactFindingAssignment::class, 'main');
        $this->factFindingAssignment->columns['CustomerAssignment_id'] = $this->customerAssignment->columns['id'];
        $this->factFindingAssignment->columns['id'] = $this->customerAssignment->columns['id'];
        $this->factFindingAssignment->columns['Customer_id'] = $this->customer->columns['id'];
        $this->factFindingAssignment->columns['Sales_id'] = $this->sales->columns['id'];
        
        $this->strikingAssignment = new EntityRecord(StrikingAssignment::class, 'main');
        $this->strikingAssignment->columns['CustomerAssignment_id'] = $this->customerAssignment->columns['id'];
        $this->strikingAssignment->columns['id'] = $this->customerAssignment->columns['id'];
        $this->strikingAssignment->columns['Customer_id'] = $this->customer->columns['id'];
        $this->strikingAssignment->columns['Sales_id'] = $this->sales->columns['id'];
        
        $this->salesActivitySchedule = new EntityRecord(SalesActivitySchedule::class, 'main');
        $this->salesActivitySchedule->columns['CustomerAssignment_id'] = $this->greetingAssignment->columns['id'];
        $this->salesActivitySchedule->columns['SalesActivity_id'] = $this->salesActivity->columns['id'];
        
        $this->salesActivityReportOne = new EntityRecord(SalesActivityReport::class, 1);
        $this->salesActivityReportOne->columns['SalesActivitySchedule_id'] = $this->salesActivitySchedule->columns['id'];
        $this->salesActivityReportTwo = new EntityRecord(SalesActivityReport::class, 2);
        $this->salesActivityReportTwo->columns['SalesActivitySchedule_id'] = $this->salesActivitySchedule->columns['id'];
        
        $this->submitNonScheduledSalesActivityReportPayload = [
            'CustomerAssignment_id' => $this->customerAssignment->columns['id'],
            'SalesActivity_id' => $this->salesActivity->columns['id'],
            'content' => 'new report content',
        ];
        
        $this->submitSalesActivityReportPayload = [
            'SalesActivitySchedule_id' => $this->salesActivitySchedule->columns['id'],
            'content' => 'report content',
        ];
    }
    protected function tearDown(): void
    {
//        parent::tearDown();
//        $this->connection->table('SalesActivity')->truncate();
//        $this->connection->table('Customer')->truncate();
//        $this->connection->table('CustomerAssignment')->truncate();
//        $this->connection->table('GreetingAssignment')->truncate();
//        $this->connection->table('FactFindingAssignment')->truncate();
//        $this->connection->table('StrikingAssignment')->truncate();
//        $this->connection->table('SalesActivitySchedule')->truncate();
//        $this->connection->table('SalesActivityReport')->truncate();
    }
    
    //
    protected function submitNonScheduledActivityReport()
    {
        $this->prepareSalesDependency();
        $this->salesActivity->insert($this->connection);
        
        $this->customerAssignment->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation (
    $CustomerAssignment_id: ID, 
    $SalesActivity_id: ID, 
    $content: String
) {
    submitNonScheduledActivityReport (
        CustomerAssignment_id: $CustomerAssignment_id, 
        SalesActivity_id: $SalesActivity_id, 
        content: $content
    ) {
        id, content, submitTime,
        salesActivitySchedule {
            startTime, endTime, status
            salesActivity { name },
        }
    }
}
_QUERY;
        $this->graphqlVariables = $this->submitNonScheduledSalesActivityReportPayload;
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_submitNonScheduledActivityReport_greetingAcitivity_200()
    {
$this->disableExceptionHandling();
        $this->greetingAssignment->insert($this->connection);
        $this->submitNonScheduledActivityReport();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'content' => $this->submitNonScheduledSalesActivityReportPayload['content'],
            'submitTime' => $this->jakartaDateTimeFormat((new DateTime())->format('Y-m-d H:i:s')),
            'salesActivitySchedule' => [
                'startTime' => $this->jakartaDateTimeFormat((new DateTime())->setTime((new DateTime())->format('H'), 00)->format('Y-m-d H:i:s')),
                'endTime' => $this->jakartaDateTimeFormat((new DateTime())->setTime((new DateTime())->format('H') + 1, 00)->format('Y-m-d H:i:s')),
                'status' => SalesActivityScheduleStatus::COMPLETED->value,
                'salesActivity' => [
                    'name' => $this->salesActivity->columns['name'],
                ],
            ],
        ]);
        
        $this->seeInDatabase('SalesActivitySchedule', [
            'CustomerAssignment_id' => $this->customerAssignment->columns['id'],
            'status' => SalesActivityScheduleStatus::COMPLETED->value,
            'startTime' => (new DateTime())->setTime((new DateTime())->format('H'), 00)->format('Y-m-d H:i:s'),
            'endTime' => (new DateTime())->setTime((new DateTime())->format('H')+1, 00)->format('Y-m-d H:i:s'),
        ]);
        
        $this->seeInDatabase('SalesActivityReport', [
            'submitTime' => $this->stringOfCurrentTime(),
            'content' => $this->submitNonScheduledSalesActivityReportPayload['content'],
        ]);
    }
    public function test_submitNonScheduledActivityReport_factFindingAcitivity_200()
    {
$this->disableExceptionHandling();
        $this->sales->columns['role'] = SalesRole::FACT_FINDER->value;
        $this->factFindingAssignment->insert($this->connection);
        $this->submitNonScheduledActivityReport();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'content' => $this->submitNonScheduledSalesActivityReportPayload['content'],
            'submitTime' => $this->jakartaDateTimeFormat((new DateTime())->format('Y-m-d H:i:s')),
            'salesActivitySchedule' => [
                'startTime' => $this->jakartaDateTimeFormat((new DateTime())->setTime((new DateTime())->format('H'), 00)->format('Y-m-d H:i:s')),
                'endTime' => $this->jakartaDateTimeFormat((new DateTime())->setTime((new DateTime())->format('H') + 1, 00)->format('Y-m-d H:i:s')),
                'status' => SalesActivityScheduleStatus::COMPLETED->value,
                'salesActivity' => [
                    'name' => $this->salesActivity->columns['name'],
                ],
            ],
        ]);
        
        $this->seeInDatabase('SalesActivitySchedule', [
            'CustomerAssignment_id' => $this->customerAssignment->columns['id'],
            'status' => SalesActivityScheduleStatus::COMPLETED->value,
            'startTime' => (new DateTime())->setTime((new DateTime())->format('H'), 00)->format('Y-m-d H:i:s'),
            'endTime' => (new DateTime())->setTime((new DateTime())->format('H')+1, 00)->format('Y-m-d H:i:s'),
        ]);
        
        $this->seeInDatabase('SalesActivityReport', [
            'submitTime' => $this->stringOfCurrentTime(),
            'content' => $this->submitNonScheduledSalesActivityReportPayload['content'],
        ]);
    }
    public function test_submitNonScheduledActivityReport_strikingAcitivity_200()
    {
$this->disableExceptionHandling();
        $this->sales->columns['role'] = SalesRole::STRIKER->value;
        $this->strikingAssignment->insert($this->connection);
        $this->submitNonScheduledActivityReport();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'content' => $this->submitNonScheduledSalesActivityReportPayload['content'],
            'submitTime' => $this->jakartaDateTimeFormat((new DateTime())->format('Y-m-d H:i:s')),
            'salesActivitySchedule' => [
                'startTime' => $this->jakartaDateTimeFormat((new DateTime())->setTime((new DateTime())->format('H'), 00)->format('Y-m-d H:i:s')),
                'endTime' => $this->jakartaDateTimeFormat((new DateTime())->setTime((new DateTime())->format('H') + 1, 00)->format('Y-m-d H:i:s')),
                'status' => SalesActivityScheduleStatus::COMPLETED->value,
                'salesActivity' => [
                    'name' => $this->salesActivity->columns['name'],
                ],
            ],
        ]);
        
        $this->seeInDatabase('SalesActivitySchedule', [
            'CustomerAssignment_id' => $this->customerAssignment->columns['id'],
            'status' => SalesActivityScheduleStatus::COMPLETED->value,
            'startTime' => (new DateTime())->setTime((new DateTime())->format('H'), 00)->format('Y-m-d H:i:s'),
            'endTime' => (new DateTime())->setTime((new DateTime())->format('H')+1, 00)->format('Y-m-d H:i:s'),
        ]);
        
        $this->seeInDatabase('SalesActivityReport', [
            'submitTime' => $this->stringOfCurrentTime(),
            'content' => $this->submitNonScheduledSalesActivityReportPayload['content'],
        ]);
    }
    
    //
    protected function submitSalesActivityReport()
    {
        $this->prepareSalesDependency();
        $this->salesActivity->insert($this->connection);
        $this->customerAssignment->insert($this->connection);
        $this->greetingAssignment->insert($this->connection);
        $this->salesActivitySchedule->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $SalesActivitySchedule_id: ID!, $content: String ) {
    submitSalesActivityReport (SalesActivitySchedule_id: $SalesActivitySchedule_id, content: $content ) {
        id, content, submitTime
    }
}
_QUERY;
        $this->graphqlVariables = $this->submitSalesActivityReportPayload;
        $this->postGraphqlRequest($this->sales->token);
    }
    public function test_submitSalesActivityReport_200()
    {
        $this->submitSalesActivityReport();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'content' => $this->submitSalesActivityReportPayload['content'],
            'submitTime' => $this->stringOfJakartaCurrentTime(),
        ]);
        
        $this->seeInDatabase('SalesActivityReport', [
            'SalesActivitySchedule_id' => $this->salesActivitySchedule->columns['id'],
            'content' => $this->submitSalesActivityReportPayload['content'],
            'submitTime' => $this->stringOfJakartaCurrentTime(),
        ]);
    }
}
