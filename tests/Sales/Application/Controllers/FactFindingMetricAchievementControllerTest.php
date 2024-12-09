<?php

namespace Sales\Application\Controllers;

use Company\Domain\Model\FactFinderMetric;
use Company\Domain\Model\Manager\Sales\CustomerAssignment;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\SalesActivitySchedule;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Company\Domain\Model\Manager\Sales\FactFindingAssignment;
use DateTime;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\RecurrenceType;
use Shared\Domain\Enum\SalesMetricType;
use Tests\Http\Record\EntityRecord;
use Tests\Sales\Application\Controllers\SalesControllerTestCase;

class FactFindingMetricAchievementControllerTest extends SalesControllerTestCase
{
    protected EntityRecord $factFinderMetricOne;
    protected EntityRecord $factFinderMetricTwo;
    
    protected EntityRecord $factFindingAssignment, $customerAssignment;
    protected EntityRecord $salesActivitySchedule;
    
    protected EntityRecord $factFindingAssignmentOngoingRecurrenceA, $customerAssignmentOngoingRecurrenceA;
    protected EntityRecord $factFindingAssignmentOngoingRecurrenceB, $customerAssignmentOngoingRecurrenceB;
    protected EntityRecord $factFindingAssignmentOngoingRecurrenceC, $customerAssignmentOngoingRecurrenceC;
    protected EntityRecord $factFindingAssignmentMinusOneRecurrenceA, $customerAssignmentMinusOneRecurrenceA;
    protected EntityRecord $factFindingAssignmentMinusTwoRecurrenceA, $customerAssignmentMinusTwoRecurrenceA;
    protected EntityRecord $factFindingAssignmentMinusTwoRecurrenceB, $customerAssignmentMinusTwoRecurrenceB;
    protected EntityRecord $factFindingAssignmentMinusThreeRecurrenceA, $customerAssignmentMinusThreeRecurrenceA;
    protected EntityRecord $factFindingAssignmentMinusThreeRecurrenceB, $customerAssignmentMinusThreeRecurrenceB;


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
        $this->connection->table('FactFinderMetric')->truncate();
        $this->connection->table('FactFindingAssignment')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
        $this->connection->table('SalesActivityReport')->truncate();
        //
        $this->factFinderMetricOne = new EntityRecord(FactFinderMetric::class, 'One');
        $this->factFinderMetricOne->columns['target'] = 111;
        $this->factFinderMetricOne->columns['dailyReminderTarget'] = 11;
        $this->factFinderMetricOne->columns['salesMetricType'] = SalesMetricType::SUCCESSFULL_ASSIGNMENT->value;
        $this->factFinderMetricOne->columns['evaluationType'] = EvaluationType::COUNT->value;
        $this->factFinderMetricOne->columns['recurrenceType'] = RecurrenceType::MONTHLY->value;
        $this->factFinderMetricOne->columns['recurrenceCount'] = 3;
        
        $this->factFinderMetricTwo = new EntityRecord(FactFinderMetric::class, 'Two');
        $this->factFinderMetricTwo->columns['target'] = 222;
        $this->factFinderMetricTwo->columns['dailyReminderTarget'] = 22;
        $this->factFinderMetricTwo->columns['salesMetricType'] = SalesMetricType::SALES_ACTIVITY->value;
        $this->factFinderMetricTwo->columns['evaluationType'] = EvaluationType::COUNT->value;
        $this->factFinderMetricTwo->columns['recurrenceType'] = RecurrenceType::MONTHLY->value;
        $this->factFinderMetricTwo->columns['recurrenceCount'] = 3;
        
        //
        $this->customerAssignment = new EntityRecord(CustomerAssignment::class, 'main');
        $this->factFindingAssignment = new EntityRecord(FactFindingAssignment::class, 'main');
        $this->factFindingAssignment->columns['id'] = $this->customerAssignment->columns['id'];
        $this->factFindingAssignment->columns['CustomerAssignment_id'] = $this->customerAssignment->columns['id'];
        $this->factFindingAssignment->columns['Sales_id'] = $this->sales->columns['id'];
        
        //
        $this->customerAssignmentOngoingRecurrenceA = new EntityRecord(CustomerAssignment::class, 'OngoingRecurrenceA');
        $this->customerAssignmentOngoingRecurrenceA->columns['completedTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->factFindingAssignmentOngoingRecurrenceA = new EntityRecord(FactFindingAssignment::class, 'OngoingRecurrenceA');
        $this->factFindingAssignmentOngoingRecurrenceA->columns['id'] = $this->customerAssignmentOngoingRecurrenceA->columns['id'];
        $this->factFindingAssignmentOngoingRecurrenceA->columns['CustomerAssignment_id'] = $this->customerAssignmentOngoingRecurrenceA->columns['id'];
        $this->factFindingAssignmentOngoingRecurrenceA->columns['Sales_id'] = $this->sales->columns['id'];
        $this->factFindingAssignmentOngoingRecurrenceA->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        
        $this->customerAssignmentOngoingRecurrenceB = new EntityRecord(CustomerAssignment::class, 'OngoingRecurrenceB');
        $this->customerAssignmentOngoingRecurrenceB->columns['completedTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->factFindingAssignmentOngoingRecurrenceB = new EntityRecord(FactFindingAssignment::class, 'OngoingRecurrenceB');
        $this->factFindingAssignmentOngoingRecurrenceB->columns['id'] = $this->customerAssignmentOngoingRecurrenceB->columns['id'];
        $this->factFindingAssignmentOngoingRecurrenceB->columns['CustomerAssignment_id'] = $this->customerAssignmentOngoingRecurrenceB->columns['id'];
        $this->factFindingAssignmentOngoingRecurrenceB->columns['Sales_id'] = $this->sales->columns['id'];
        $this->factFindingAssignmentOngoingRecurrenceB->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        
        $this->customerAssignmentOngoingRecurrenceC = new EntityRecord(CustomerAssignment::class, 'OngoingRecurrenceC');
        $this->customerAssignmentOngoingRecurrenceC->columns['completedTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->factFindingAssignmentOngoingRecurrenceC = new EntityRecord(FactFindingAssignment::class, 'OngoingRecurrenceC');
        $this->factFindingAssignmentOngoingRecurrenceC->columns['id'] = $this->customerAssignmentOngoingRecurrenceC->columns['id'];
        $this->factFindingAssignmentOngoingRecurrenceC->columns['CustomerAssignment_id'] = $this->customerAssignmentOngoingRecurrenceC->columns['id'];
        $this->factFindingAssignmentOngoingRecurrenceC->columns['Sales_id'] = $this->sales->columns['id'];
        $this->factFindingAssignmentOngoingRecurrenceC->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        
        $this->customerAssignmentMinusOneRecurrenceA = new EntityRecord(CustomerAssignment::class, 'MinusOneRecurrenceA');
        $this->customerAssignmentMinusOneRecurrenceA->columns['completedTime'] = (new DateTime('first day of -1 months'))->format('Y-m-d H:i:s');
        $this->factFindingAssignmentMinusOneRecurrenceA = new EntityRecord(FactFindingAssignment::class, 'MinusOneRecurrenceA');
        $this->factFindingAssignmentMinusOneRecurrenceA->columns['id'] = $this->customerAssignmentMinusOneRecurrenceA->columns['id'];
        $this->factFindingAssignmentMinusOneRecurrenceA->columns['CustomerAssignment_id'] = $this->customerAssignmentMinusOneRecurrenceA->columns['id'];
        $this->factFindingAssignmentMinusOneRecurrenceA->columns['Sales_id'] = $this->sales->columns['id'];
        $this->factFindingAssignmentMinusOneRecurrenceA->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        
        $this->customerAssignmentMinusTwoRecurrenceA = new EntityRecord(CustomerAssignment::class, 'MinusTwoRecurrenceA');
        $this->customerAssignmentMinusTwoRecurrenceA->columns['completedTime'] = (new DateTime('first day of -2 months'))->format('Y-m-d H:i:s');
        $this->factFindingAssignmentMinusTwoRecurrenceA = new EntityRecord(FactFindingAssignment::class, 'MinusTwoRecurrenceA');
        $this->factFindingAssignmentMinusTwoRecurrenceA->columns['id'] = $this->customerAssignmentMinusTwoRecurrenceA->columns['id'];
        $this->factFindingAssignmentMinusTwoRecurrenceA->columns['CustomerAssignment_id'] = $this->customerAssignmentMinusTwoRecurrenceA->columns['id'];
        $this->factFindingAssignmentMinusTwoRecurrenceA->columns['Sales_id'] = $this->sales->columns['id'];
        $this->factFindingAssignmentMinusTwoRecurrenceA->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        
        $this->customerAssignmentMinusTwoRecurrenceB = new EntityRecord(CustomerAssignment::class, 'MinusTwoRecurrenceB');
        $this->customerAssignmentMinusTwoRecurrenceB->columns['completedTime'] = (new DateTime('first day of -2 months'))->format('Y-m-d H:i:s');
        $this->factFindingAssignmentMinusTwoRecurrenceB = new EntityRecord(FactFindingAssignment::class, 'MinusTwoRecurrenceB');
        $this->factFindingAssignmentMinusTwoRecurrenceB->columns['id'] = $this->customerAssignmentMinusTwoRecurrenceB->columns['id'];
        $this->factFindingAssignmentMinusTwoRecurrenceB->columns['CustomerAssignment_id'] = $this->customerAssignmentMinusTwoRecurrenceB->columns['id'];
        $this->factFindingAssignmentMinusTwoRecurrenceB->columns['Sales_id'] = $this->sales->columns['id'];
        $this->factFindingAssignmentMinusTwoRecurrenceB->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        
        $this->customerAssignmentMinusThreeRecurrenceA = new EntityRecord(CustomerAssignment::class, 'MinusThreeRecurrenceA');
        $this->customerAssignmentMinusThreeRecurrenceA->columns['completedTime'] = (new DateTime('first day of -3 months'))->format('Y-m-d H:i:s');
        $this->factFindingAssignmentMinusThreeRecurrenceA = new EntityRecord(FactFindingAssignment::class, 'MinusThreeRecurrenceA');
        $this->factFindingAssignmentMinusThreeRecurrenceA->columns['id'] = $this->customerAssignmentMinusThreeRecurrenceA->columns['id'];
        $this->factFindingAssignmentMinusThreeRecurrenceA->columns['CustomerAssignment_id'] = $this->customerAssignmentMinusThreeRecurrenceA->columns['id'];
        $this->factFindingAssignmentMinusThreeRecurrenceA->columns['Sales_id'] = $this->sales->columns['id'];
        $this->factFindingAssignmentMinusThreeRecurrenceA->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        
        $this->customerAssignmentMinusThreeRecurrenceB = new EntityRecord(CustomerAssignment::class, 'MinusThreeRecurrenceB');
        $this->customerAssignmentMinusThreeRecurrenceB->columns['completedTime'] = (new DateTime('first day of -3 months'))->format('Y-m-d H:i:s');
        $this->factFindingAssignmentMinusThreeRecurrenceB = new EntityRecord(FactFindingAssignment::class, 'MinusThreeRecurrenceB');
        $this->factFindingAssignmentMinusThreeRecurrenceB->columns['id'] = $this->customerAssignmentMinusThreeRecurrenceB->columns['id'];
        $this->factFindingAssignmentMinusThreeRecurrenceB->columns['CustomerAssignment_id'] = $this->customerAssignmentMinusThreeRecurrenceB->columns['id'];
        $this->factFindingAssignmentMinusThreeRecurrenceB->columns['Sales_id'] = $this->sales->columns['id'];
        $this->factFindingAssignmentMinusThreeRecurrenceB->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        
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
        parent::tearDown();
        $this->connection->table('FactFinderMetric')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('FactFindingAssignment')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
        $this->connection->table('SalesActivityReport')->truncate();
        $this->connection->table('ClosingRequest')->truncate();
    }
    
    protected function viewAllFactFinderMetricSummary()
    {
        $this->prepareSalesDependency();
        //
        $this->factFinderMetricOne->insert($this->connection);
        $this->factFinderMetricTwo->insert($this->connection);
        
        //
        $this->customerAssignmentOngoingRecurrenceA->insert($this->connection);
        $this->factFindingAssignmentOngoingRecurrenceA->insert($this->connection);
        $this->customerAssignmentOngoingRecurrenceB->insert($this->connection);
        $this->factFindingAssignmentOngoingRecurrenceB->insert($this->connection);
        $this->customerAssignmentOngoingRecurrenceC->insert($this->connection);
        $this->factFindingAssignmentOngoingRecurrenceC->insert($this->connection);
        $this->customerAssignmentMinusOneRecurrenceA->insert($this->connection);
        $this->factFindingAssignmentMinusOneRecurrenceA->insert($this->connection);
        $this->customerAssignmentMinusTwoRecurrenceA->insert($this->connection);
        $this->factFindingAssignmentMinusTwoRecurrenceA->insert($this->connection);
        $this->customerAssignmentMinusTwoRecurrenceB->insert($this->connection);
        $this->factFindingAssignmentMinusTwoRecurrenceB->insert($this->connection);
        $this->customerAssignmentMinusThreeRecurrenceA->insert($this->connection);
        $this->factFindingAssignmentMinusThreeRecurrenceA->insert($this->connection);
        $this->customerAssignmentMinusThreeRecurrenceB->insert($this->connection);
        $this->factFindingAssignmentMinusThreeRecurrenceB->insert($this->connection);
        
        //
        $this->customerAssignment->insert($this->connection);
        $this->factFindingAssignment->insert($this->connection);
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
        $this->response = $this->get("api/view-all-fact-finding-metric-achievement", $this->sales->token);
    }
    public function test_viewAllFactFinderMetricSummary_200()
    {
$this->disableExceptionHandling();
        $this->viewAllFactFinderMetricSummary();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            "dailyAchievement" => 3,
            "dailyReminderTarget" => 11,
            'name' => $this->factFinderMetricOne->columns['name'],
            'target' => $this->factFinderMetricOne->columns['target'],
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
            'name' => $this->factFinderMetricTwo->columns['name'],
            'target' => $this->factFinderMetricTwo->columns['target'],
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
