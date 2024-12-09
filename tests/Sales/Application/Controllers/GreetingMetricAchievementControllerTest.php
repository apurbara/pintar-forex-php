<?php

namespace Sales\Application\Controllers;

//use Company\Domain\Model\GreeterMetric;
//use Company\Domain\Model\Manager\Sales\CustomerAssignment\SalesActivitySchedule;
//use Company\Domain\Model\Manager\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
//use DateTime;
//use Sales\Domain\Model\Sales\CustomerAssignment;
//use Sales\Domain\Model\Sales\CustomerAssignment\ClosingRequest;
//use Shared\Domain\Enum\EvaluationType;
//use Shared\Domain\Enum\ManagementApprovalStatus;
//use Shared\Domain\Enum\MetricType;
//use Shared\Domain\Enum\RecurrenceType;


use Company\Domain\Model\GreeterMetric;
use Company\Domain\Model\Manager\Sales\CustomerAssignment;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\SalesActivitySchedule;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Company\Domain\Model\Manager\Sales\GreetingAssignment;
use DateTime;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\RecurrenceType;
use Shared\Domain\Enum\SalesMetricType;
use Tests\Http\Record\EntityRecord;
use Tests\Sales\Application\Controllers\SalesControllerTestCase;

class GreetingMetricAchievementControllerTest extends SalesControllerTestCase
{
    protected EntityRecord $greeterMetricOne;
    protected EntityRecord $greeterMetricTwo;
    
    protected EntityRecord $greetingAssignment, $customerAssignment;
    protected EntityRecord $salesActivitySchedule;
    
    protected EntityRecord $greetingAssignmentOngoingRecurrenceA, $customerAssignmentOngoingRecurrenceA;
    protected EntityRecord $greetingAssignmentOngoingRecurrenceB, $customerAssignmentOngoingRecurrenceB;
    protected EntityRecord $greetingAssignmentOngoingRecurrenceC, $customerAssignmentOngoingRecurrenceC;
    protected EntityRecord $greetingAssignmentMinusOneRecurrenceA, $customerAssignmentMinusOneRecurrenceA;
    protected EntityRecord $greetingAssignmentMinusTwoRecurrenceA, $customerAssignmentMinusTwoRecurrenceA;
    protected EntityRecord $greetingAssignmentMinusTwoRecurrenceB, $customerAssignmentMinusTwoRecurrenceB;
    protected EntityRecord $greetingAssignmentMinusThreeRecurrenceA, $customerAssignmentMinusThreeRecurrenceA;
    protected EntityRecord $greetingAssignmentMinusThreeRecurrenceB, $customerAssignmentMinusThreeRecurrenceB;


    protected EntityRecord $salesActivityReportOngoingRecurrenceA;
    protected EntityRecord $salesActivityReportOngoingRecurrenceB;
    protected EntityRecord $salesActivityReportOngoingRecurrenceC;
    protected EntityRecord $salesActivityReportMinusOneRecurrenceA;
    protected EntityRecord $salesActivityReportMinusOneRecurrenceB;
    protected EntityRecord $salesActivityReportMinusTwoRecurrenceA;
    protected EntityRecord $salesActivityReportMinusTwoRecurrenceB;
    protected EntityRecord $salesActivityReportMinusThreeRecurrenceA;
    protected EntityRecord $salesActivityReportMinusThreeRecurrenceB;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('GreeterMetric')->truncate();
        $this->connection->table('GreetingAssignment')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
        $this->connection->table('SalesActivityReport')->truncate();
        //
        $this->greeterMetricOne = new EntityRecord(GreeterMetric::class, 'One');
        $this->greeterMetricOne->columns['target'] = 111;
        $this->greeterMetricOne->columns['dailyReminderTarget'] = 11;
        $this->greeterMetricOne->columns['salesMetricType'] = SalesMetricType::SUCCESSFULL_ASSIGNMENT->value;
        $this->greeterMetricOne->columns['evaluationType'] = EvaluationType::COUNT->value;
        $this->greeterMetricOne->columns['recurrenceType'] = RecurrenceType::MONTHLY->value;
        $this->greeterMetricOne->columns['recurrenceCount'] = 3;
        
        $this->greeterMetricTwo = new EntityRecord(GreeterMetric::class, 'Two');
        $this->greeterMetricTwo->columns['target'] = 222;
        $this->greeterMetricTwo->columns['dailyReminderTarget'] = 22;
        $this->greeterMetricTwo->columns['salesMetricType'] = SalesMetricType::SALES_ACTIVITY->value;
        $this->greeterMetricTwo->columns['evaluationType'] = EvaluationType::COUNT->value;
        $this->greeterMetricTwo->columns['recurrenceType'] = RecurrenceType::MONTHLY->value;
        $this->greeterMetricTwo->columns['recurrenceCount'] = 3;
        
        //
        $this->customerAssignment = new EntityRecord(CustomerAssignment::class, 'main');
        $this->greetingAssignment = new EntityRecord(GreetingAssignment::class, 'main');
        $this->greetingAssignment->columns['id'] = $this->customerAssignment->columns['id'];
        $this->greetingAssignment->columns['CustomerAssignment_id'] = $this->customerAssignment->columns['id'];
        $this->greetingAssignment->columns['Sales_id'] = $this->sales->columns['id'];
        
        //
        $this->customerAssignmentOngoingRecurrenceA = new EntityRecord(CustomerAssignment::class, 'OngoingRecurrenceA');
        $this->customerAssignmentOngoingRecurrenceA->columns['completedTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->greetingAssignmentOngoingRecurrenceA = new EntityRecord(GreetingAssignment::class, 'OngoingRecurrenceA');
        $this->greetingAssignmentOngoingRecurrenceA->columns['id'] = $this->customerAssignmentOngoingRecurrenceA->columns['id'];
        $this->greetingAssignmentOngoingRecurrenceA->columns['CustomerAssignment_id'] = $this->customerAssignmentOngoingRecurrenceA->columns['id'];
        $this->greetingAssignmentOngoingRecurrenceA->columns['Sales_id'] = $this->sales->columns['id'];
        $this->greetingAssignmentOngoingRecurrenceA->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        
        $this->customerAssignmentOngoingRecurrenceB = new EntityRecord(CustomerAssignment::class, 'OngoingRecurrenceB');
        $this->customerAssignmentOngoingRecurrenceB->columns['completedTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->greetingAssignmentOngoingRecurrenceB = new EntityRecord(GreetingAssignment::class, 'OngoingRecurrenceB');
        $this->greetingAssignmentOngoingRecurrenceB->columns['id'] = $this->customerAssignmentOngoingRecurrenceB->columns['id'];
        $this->greetingAssignmentOngoingRecurrenceB->columns['CustomerAssignment_id'] = $this->customerAssignmentOngoingRecurrenceB->columns['id'];
        $this->greetingAssignmentOngoingRecurrenceB->columns['Sales_id'] = $this->sales->columns['id'];
        $this->greetingAssignmentOngoingRecurrenceB->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        
        $this->customerAssignmentOngoingRecurrenceC = new EntityRecord(CustomerAssignment::class, 'OngoingRecurrenceC');
        $this->customerAssignmentOngoingRecurrenceC->columns['completedTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->greetingAssignmentOngoingRecurrenceC = new EntityRecord(GreetingAssignment::class, 'OngoingRecurrenceC');
        $this->greetingAssignmentOngoingRecurrenceC->columns['id'] = $this->customerAssignmentOngoingRecurrenceC->columns['id'];
        $this->greetingAssignmentOngoingRecurrenceC->columns['CustomerAssignment_id'] = $this->customerAssignmentOngoingRecurrenceC->columns['id'];
        $this->greetingAssignmentOngoingRecurrenceC->columns['Sales_id'] = $this->sales->columns['id'];
        $this->greetingAssignmentOngoingRecurrenceC->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        
        $this->customerAssignmentMinusOneRecurrenceA = new EntityRecord(CustomerAssignment::class, 'MinusOneRecurrenceA');
        $this->customerAssignmentMinusOneRecurrenceA->columns['completedTime'] = (new DateTime('first day of -1 months'))->format('Y-m-d H:i:s');
        $this->greetingAssignmentMinusOneRecurrenceA = new EntityRecord(GreetingAssignment::class, 'MinusOneRecurrenceA');
        $this->greetingAssignmentMinusOneRecurrenceA->columns['id'] = $this->customerAssignmentMinusOneRecurrenceA->columns['id'];
        $this->greetingAssignmentMinusOneRecurrenceA->columns['CustomerAssignment_id'] = $this->customerAssignmentMinusOneRecurrenceA->columns['id'];
        $this->greetingAssignmentMinusOneRecurrenceA->columns['Sales_id'] = $this->sales->columns['id'];
        $this->greetingAssignmentMinusOneRecurrenceA->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        
        $this->customerAssignmentMinusTwoRecurrenceA = new EntityRecord(CustomerAssignment::class, 'MinusTwoRecurrenceA');
        $this->customerAssignmentMinusTwoRecurrenceA->columns['completedTime'] = (new DateTime('first day of -2 months'))->format('Y-m-d H:i:s');
        $this->greetingAssignmentMinusTwoRecurrenceA = new EntityRecord(GreetingAssignment::class, 'MinusTwoRecurrenceA');
        $this->greetingAssignmentMinusTwoRecurrenceA->columns['id'] = $this->customerAssignmentMinusTwoRecurrenceA->columns['id'];
        $this->greetingAssignmentMinusTwoRecurrenceA->columns['CustomerAssignment_id'] = $this->customerAssignmentMinusTwoRecurrenceA->columns['id'];
        $this->greetingAssignmentMinusTwoRecurrenceA->columns['Sales_id'] = $this->sales->columns['id'];
        $this->greetingAssignmentMinusTwoRecurrenceA->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        
        $this->customerAssignmentMinusTwoRecurrenceB = new EntityRecord(CustomerAssignment::class, 'MinusTwoRecurrenceB');
        $this->customerAssignmentMinusTwoRecurrenceB->columns['completedTime'] = (new DateTime('first day of -2 months'))->format('Y-m-d H:i:s');
        $this->greetingAssignmentMinusTwoRecurrenceB = new EntityRecord(GreetingAssignment::class, 'MinusTwoRecurrenceB');
        $this->greetingAssignmentMinusTwoRecurrenceB->columns['id'] = $this->customerAssignmentMinusTwoRecurrenceB->columns['id'];
        $this->greetingAssignmentMinusTwoRecurrenceB->columns['CustomerAssignment_id'] = $this->customerAssignmentMinusTwoRecurrenceB->columns['id'];
        $this->greetingAssignmentMinusTwoRecurrenceB->columns['Sales_id'] = $this->sales->columns['id'];
        $this->greetingAssignmentMinusTwoRecurrenceB->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        
        $this->customerAssignmentMinusThreeRecurrenceA = new EntityRecord(CustomerAssignment::class, 'MinusThreeRecurrenceA');
        $this->customerAssignmentMinusThreeRecurrenceA->columns['completedTime'] = (new DateTime('first day of -3 months'))->format('Y-m-d H:i:s');
        $this->greetingAssignmentMinusThreeRecurrenceA = new EntityRecord(GreetingAssignment::class, 'MinusThreeRecurrenceA');
        $this->greetingAssignmentMinusThreeRecurrenceA->columns['id'] = $this->customerAssignmentMinusThreeRecurrenceA->columns['id'];
        $this->greetingAssignmentMinusThreeRecurrenceA->columns['CustomerAssignment_id'] = $this->customerAssignmentMinusThreeRecurrenceA->columns['id'];
        $this->greetingAssignmentMinusThreeRecurrenceA->columns['Sales_id'] = $this->sales->columns['id'];
        $this->greetingAssignmentMinusThreeRecurrenceA->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        
        $this->customerAssignmentMinusThreeRecurrenceB = new EntityRecord(CustomerAssignment::class, 'MinusThreeRecurrenceB');
        $this->customerAssignmentMinusThreeRecurrenceB->columns['completedTime'] = (new DateTime('first day of -3 months'))->format('Y-m-d H:i:s');
        $this->greetingAssignmentMinusThreeRecurrenceB = new EntityRecord(GreetingAssignment::class, 'MinusThreeRecurrenceB');
        $this->greetingAssignmentMinusThreeRecurrenceB->columns['id'] = $this->customerAssignmentMinusThreeRecurrenceB->columns['id'];
        $this->greetingAssignmentMinusThreeRecurrenceB->columns['CustomerAssignment_id'] = $this->customerAssignmentMinusThreeRecurrenceB->columns['id'];
        $this->greetingAssignmentMinusThreeRecurrenceB->columns['Sales_id'] = $this->sales->columns['id'];
        $this->greetingAssignmentMinusThreeRecurrenceB->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        
        //
        $this->salesActivitySchedule = new EntityRecord(SalesActivitySchedule::class, 'main');
        $this->salesActivitySchedule->columns['CustomerAssignment_id'] = $this->customerAssignment->columns['id'];
        
        $this->salesActivityReportOngoingRecurrenceA = new EntityRecord(SalesActivityReport::class, 'ongoingRecurrenceA');
        $this->salesActivityReportOngoingRecurrenceA->columns['SalesActivitySchedule_id'] = $this->salesActivitySchedule->columns['id'];
        $this->salesActivityReportOngoingRecurrenceA->columns['submitTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->salesActivityReportOngoingRecurrenceB = new EntityRecord(SalesActivityReport::class, 'ongoingRecurrenceB');
        $this->salesActivityReportOngoingRecurrenceB->columns['SalesActivitySchedule_id'] = $this->salesActivitySchedule->columns['id'];
        $this->salesActivityReportOngoingRecurrenceB->columns['submitTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->salesActivityReportOngoingRecurrenceC = new EntityRecord(SalesActivityReport::class, 'ongoingRecurrenceC');
        $this->salesActivityReportOngoingRecurrenceC->columns['SalesActivitySchedule_id'] = $this->salesActivitySchedule->columns['id'];
        $this->salesActivityReportOngoingRecurrenceC->columns['submitTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->salesActivityReportMinusOneRecurrenceA = new EntityRecord(SalesActivityReport::class, 'minusOneRecurrenceA');
        $this->salesActivityReportMinusOneRecurrenceA->columns['SalesActivitySchedule_id'] = $this->salesActivitySchedule->columns['id'];
        $this->salesActivityReportMinusOneRecurrenceA->columns['submitTime'] = (new DateTime('first day of -1 months'))->format('Y-m-d H:i:s');
        $this->salesActivityReportMinusOneRecurrenceB = new EntityRecord(SalesActivityReport::class, 'minusOneRecurrenceB');
        $this->salesActivityReportMinusOneRecurrenceB->columns['SalesActivitySchedule_id'] = $this->salesActivitySchedule->columns['id'];
        $this->salesActivityReportMinusOneRecurrenceB->columns['submitTime'] = (new DateTime('first day of -1 months'))->format('Y-m-d H:i:s');
        $this->salesActivityReportMinusTwoRecurrenceA = new EntityRecord(SalesActivityReport::class, 'minusTwoRecurrenceA');
        $this->salesActivityReportMinusTwoRecurrenceA->columns['SalesActivitySchedule_id'] = $this->salesActivitySchedule->columns['id'];
        $this->salesActivityReportMinusTwoRecurrenceA->columns['submitTime'] = (new DateTime('first day of -2 months'))->format('Y-m-d H:i:s');
        $this->salesActivityReportMinusTwoRecurrenceB = new EntityRecord(SalesActivityReport::class, 'minusTwoRecurrenceB');
        $this->salesActivityReportMinusTwoRecurrenceB->columns['SalesActivitySchedule_id'] = $this->salesActivitySchedule->columns['id'];
        $this->salesActivityReportMinusTwoRecurrenceB->columns['submitTime'] = (new DateTime('first day of -2 months'))->format('Y-m-d H:i:s');
        $this->salesActivityReportMinusThreeRecurrenceA = new EntityRecord(SalesActivityReport::class, 'minusThreeRecurrenceA');
        $this->salesActivityReportMinusThreeRecurrenceA->columns['SalesActivitySchedule_id'] = $this->salesActivitySchedule->columns['id'];
        $this->salesActivityReportMinusThreeRecurrenceA->columns['submitTime'] = (new DateTime('first day of -3 months'))->format('Y-m-d H:i:s');
        $this->salesActivityReportMinusThreeRecurrenceB = new EntityRecord(SalesActivityReport::class, 'minusThreeRecurrenceB');
        $this->salesActivityReportMinusThreeRecurrenceB->columns['SalesActivitySchedule_id'] = $this->salesActivitySchedule->columns['id'];
        $this->salesActivityReportMinusThreeRecurrenceB->columns['submitTime'] = (new DateTime('first day of -3 months'))->format('Y-m-d H:i:s');
    }
    
    protected function tearDown(): void
    {
//        parent::tearDown();
//        $this->connection->table('GreeterMetric')->truncate();
//        $this->connection->table('CustomerAssignment')->truncate();
//        $this->connection->table('GreetingAssignment')->truncate();
//        $this->connection->table('SalesActivitySchedule')->truncate();
//        $this->connection->table('SalesActivityReport')->truncate();
//        $this->connection->table('ClosingRequest')->truncate();
    }
    
    protected function viewAllGreeterMetricSummary()
    {
        $this->prepareSalesDependency();
        //
        $this->greeterMetricOne->insert($this->connection);
        $this->greeterMetricTwo->insert($this->connection);
        
        //
        $this->customerAssignmentOngoingRecurrenceA->insert($this->connection);
        $this->greetingAssignmentOngoingRecurrenceA->insert($this->connection);
        $this->customerAssignmentOngoingRecurrenceB->insert($this->connection);
        $this->greetingAssignmentOngoingRecurrenceB->insert($this->connection);
        $this->customerAssignmentOngoingRecurrenceC->insert($this->connection);
        $this->greetingAssignmentOngoingRecurrenceC->insert($this->connection);
        $this->customerAssignmentMinusOneRecurrenceA->insert($this->connection);
        $this->greetingAssignmentMinusOneRecurrenceA->insert($this->connection);
        $this->customerAssignmentMinusTwoRecurrenceA->insert($this->connection);
        $this->greetingAssignmentMinusTwoRecurrenceA->insert($this->connection);
        $this->customerAssignmentMinusTwoRecurrenceB->insert($this->connection);
        $this->greetingAssignmentMinusTwoRecurrenceB->insert($this->connection);
        $this->customerAssignmentMinusThreeRecurrenceA->insert($this->connection);
        $this->greetingAssignmentMinusThreeRecurrenceA->insert($this->connection);
        $this->customerAssignmentMinusThreeRecurrenceB->insert($this->connection);
        $this->greetingAssignmentMinusThreeRecurrenceB->insert($this->connection);
        
        //
        $this->customerAssignment->insert($this->connection);
        $this->greetingAssignment->insert($this->connection);
        $this->salesActivitySchedule->insert($this->connection);
        
        $this->salesActivityReportOngoingRecurrenceA->insert($this->connection);
        $this->salesActivityReportOngoingRecurrenceB->insert($this->connection);
        $this->salesActivityReportOngoingRecurrenceC->insert($this->connection);
        $this->salesActivityReportMinusOneRecurrenceA->insert($this->connection);
        $this->salesActivityReportMinusOneRecurrenceB->insert($this->connection);
        $this->salesActivityReportMinusTwoRecurrenceA->insert($this->connection);
        $this->salesActivityReportMinusTwoRecurrenceB->insert($this->connection);
        $this->salesActivityReportMinusThreeRecurrenceA->insert($this->connection);
        $this->salesActivityReportMinusThreeRecurrenceB->insert($this->connection);
        //
        $this->response = $this->get("api/view-all-greeting-metric-achievement", $this->sales->token);
    }
    public function test_viewAllGreeterMetricSummary_200()
    {
$this->disableExceptionHandling();
        $this->viewAllGreeterMetricSummary();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            "dailyAchievement" => 3,
            "dailyReminderTarget" => 11,
            'name' => $this->greeterMetricOne->columns['name'],
            'target' => $this->greeterMetricOne->columns['target'],
            'result' => [
                [
                    'evaluationTime' => (new DateTime())->format('Y-m'),
                    'achievement' => 3,
                ],
                [
                    'evaluationTime' => (new DateTime('-1 months'))->format('Y-m'),
                    'achievement' => 1,
                ],
                [
                    'evaluationTime' => (new DateTime('-2 months'))->format('Y-m'),
                    'achievement' => 2,
                ],
            ],
        ]);
        $this->seeJsonContains([
            "dailyAchievement" => 3,
            "dailyReminderTarget" => 22,
            'name' => $this->greeterMetricTwo->columns['name'],
            'target' => $this->greeterMetricTwo->columns['target'],
            'result' => [
                [
                    'evaluationTime' => (new DateTime())->format('Y-m'),
                    'achievement' => 3,
                ],
                [
                    'evaluationTime' => (new DateTime('-1 months'))->format('Y-m'),
                    'achievement' => 2,
                ],
                [
                    'evaluationTime' => (new DateTime('-2 months'))->format('Y-m'),
                    'achievement' => 2,
                ],
            ],
        ]);
    }
    
}
