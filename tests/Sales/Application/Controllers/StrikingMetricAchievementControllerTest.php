<?php

namespace Sales\Application\Controllers;

use Company\Domain\Model\Manager\Sales\CustomerAssignment;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\SalesActivitySchedule;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Company\Domain\Model\Manager\Sales\StrikingAssignment;
use Company\Domain\Model\Manager\Sales\StrikingAssignment\ClosingRequest;
use Company\Domain\Model\StrikerMetric;
use DateTime;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Shared\Domain\Enum\RecurrenceType;
use Shared\Domain\Enum\SalesMetricType;
use Tests\Http\Record\EntityRecord;
use Tests\Sales\Application\Controllers\SalesControllerTestCase;

class StrikingMetricAchievementControllerTest extends SalesControllerTestCase
{
    protected EntityRecord $strikerMetricOne;
    protected EntityRecord $strikerMetricTwo;
    protected EntityRecord $strikerMetricThree;
    
    protected EntityRecord $strikingAssignment, $customerAssignment;
    protected EntityRecord $salesActivitySchedule;
    
    protected $strikingAssignmentOngoingRecurrenceA, $customerAssignmentOngoingRecurrenceA, $closingRequestOngoingRecurrenceA, $transactionValueOngoingRecurrenceA = 100000000;
    protected $strikingAssignmentOngoingRecurrenceB, $customerAssignmentOngoingRecurrenceB, $closingRequestOngoingRecurrenceB, $transactionValueOngoingRecurrenceB = 110000000;
    protected $strikingAssignmentOngoingRecurrenceC, $customerAssignmentOngoingRecurrenceC, $closingRequestOngoingRecurrenceC, $transactionValueOngoingRecurrenceC = 120000000;
    protected $strikingAssignmentMinusOneRecurrenceA, $customerAssignmentMinusOneRecurrenceA, $closingRequestMinusOneRecurrenceA, $transactionValueMinusOneRecurrenceA = 200000000;
    protected $strikingAssignmentMinusTwoRecurrenceA, $customerAssignmentMinusTwoRecurrenceA, $closingRequestMinusTwoRecurrenceA, $transactionValueMinusTwoRecurrenceA = 300000000;
    protected $strikingAssignmentMinusTwoRecurrenceB, $customerAssignmentMinusTwoRecurrenceB, $closingRequestMinusTwoRecurrenceB, $transactionValueMinusTwoRecurrenceB = 310000000;
    protected $strikingAssignmentMinusThreeRecurrenceA, $customerAssignmentMinusThreeRecurrenceA, $closingRequestMinusThreeRecurrenceA, $transactionValueMinusThreeRecurrenceA = 400000000;
    protected $strikingAssignmentMinusThreeRecurrenceB, $customerAssignmentMinusThreeRecurrenceB, $closingRequestMinusThreeRecurrenceB, $transactionValueMinusThreeRecurrenceB = 410000000;


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
        $this->connection->table('StrikerMetric')->truncate();
        $this->connection->table('StrikingAssignment')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
        $this->connection->table('SalesActivityReport')->truncate();
        $this->connection->table('ClosingRequest')->truncate();
        //
        $this->strikerMetricOne = new EntityRecord(StrikerMetric::class, 'One');
        $this->strikerMetricOne->columns['target'] = 111;
        $this->strikerMetricOne->columns['dailyReminderTarget'] = 11;
        $this->strikerMetricOne->columns['salesMetricType'] = SalesMetricType::SUCCESSFULL_ASSIGNMENT->value;
        $this->strikerMetricOne->columns['evaluationType'] = EvaluationType::COUNT->value;
        $this->strikerMetricOne->columns['recurrenceType'] = RecurrenceType::MONTHLY->value;
        $this->strikerMetricOne->columns['recurrenceCount'] = 3;
        
        $this->strikerMetricTwo = new EntityRecord(StrikerMetric::class, 'Two');
        $this->strikerMetricTwo->columns['target'] = 222;
        $this->strikerMetricTwo->columns['dailyReminderTarget'] = 22;
        $this->strikerMetricTwo->columns['salesMetricType'] = SalesMetricType::SALES_ACTIVITY->value;
        $this->strikerMetricTwo->columns['evaluationType'] = EvaluationType::COUNT->value;
        $this->strikerMetricTwo->columns['recurrenceType'] = RecurrenceType::MONTHLY->value;
        $this->strikerMetricTwo->columns['recurrenceCount'] = 3;
        
        $this->strikerMetricThree = new EntityRecord(StrikerMetric::class, 'Three');
        $this->strikerMetricThree->columns['target'] = 500000000;
        $this->strikerMetricThree->columns['dailyReminderTarget'] = 50000000;
        $this->strikerMetricThree->columns['salesMetricType'] = SalesMetricType::SUCCESSFULL_ASSIGNMENT->value;
        $this->strikerMetricThree->columns['evaluationType'] = EvaluationType::SUM->value;
        $this->strikerMetricThree->columns['recurrenceType'] = RecurrenceType::MONTHLY->value;
        $this->strikerMetricThree->columns['recurrenceCount'] = 3;
        
        //
        $this->customerAssignment = new EntityRecord(CustomerAssignment::class, 'main');
        $this->strikingAssignment = new EntityRecord(StrikingAssignment::class, 'main');
        $this->strikingAssignment->columns['id'] = $this->customerAssignment->columns['id'];
        $this->strikingAssignment->columns['CustomerAssignment_id'] = $this->customerAssignment->columns['id'];
        $this->strikingAssignment->columns['Sales_id'] = $this->sales->columns['id'];
        
        //
        $this->customerAssignmentOngoingRecurrenceA = new EntityRecord(CustomerAssignment::class, 'OngoingRecurrenceA');
        $this->customerAssignmentOngoingRecurrenceA->columns['completedTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->strikingAssignmentOngoingRecurrenceA = new EntityRecord(StrikingAssignment::class, 'OngoingRecurrenceA');
        $this->strikingAssignmentOngoingRecurrenceA->columns['id'] = $this->customerAssignmentOngoingRecurrenceA->columns['id'];
        $this->strikingAssignmentOngoingRecurrenceA->columns['CustomerAssignment_id'] = $this->customerAssignmentOngoingRecurrenceA->columns['id'];
        $this->strikingAssignmentOngoingRecurrenceA->columns['Sales_id'] = $this->sales->columns['id'];
        $this->strikingAssignmentOngoingRecurrenceA->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        
        $this->customerAssignmentOngoingRecurrenceB = new EntityRecord(CustomerAssignment::class, 'OngoingRecurrenceB');
        $this->customerAssignmentOngoingRecurrenceB->columns['completedTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->strikingAssignmentOngoingRecurrenceB = new EntityRecord(StrikingAssignment::class, 'OngoingRecurrenceB');
        $this->strikingAssignmentOngoingRecurrenceB->columns['id'] = $this->customerAssignmentOngoingRecurrenceB->columns['id'];
        $this->strikingAssignmentOngoingRecurrenceB->columns['CustomerAssignment_id'] = $this->customerAssignmentOngoingRecurrenceB->columns['id'];
        $this->strikingAssignmentOngoingRecurrenceB->columns['Sales_id'] = $this->sales->columns['id'];
        $this->strikingAssignmentOngoingRecurrenceB->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        
        $this->customerAssignmentOngoingRecurrenceC = new EntityRecord(CustomerAssignment::class, 'OngoingRecurrenceC');
        $this->customerAssignmentOngoingRecurrenceC->columns['completedTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->strikingAssignmentOngoingRecurrenceC = new EntityRecord(StrikingAssignment::class, 'OngoingRecurrenceC');
        $this->strikingAssignmentOngoingRecurrenceC->columns['id'] = $this->customerAssignmentOngoingRecurrenceC->columns['id'];
        $this->strikingAssignmentOngoingRecurrenceC->columns['CustomerAssignment_id'] = $this->customerAssignmentOngoingRecurrenceC->columns['id'];
        $this->strikingAssignmentOngoingRecurrenceC->columns['Sales_id'] = $this->sales->columns['id'];
        $this->strikingAssignmentOngoingRecurrenceC->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        
        $this->customerAssignmentMinusOneRecurrenceA = new EntityRecord(CustomerAssignment::class, 'MinusOneRecurrenceA');
        $this->customerAssignmentMinusOneRecurrenceA->columns['completedTime'] = (new DateTime('first day of -1 months'))->format('Y-m-d H:i:s');
        $this->strikingAssignmentMinusOneRecurrenceA = new EntityRecord(StrikingAssignment::class, 'MinusOneRecurrenceA');
        $this->strikingAssignmentMinusOneRecurrenceA->columns['id'] = $this->customerAssignmentMinusOneRecurrenceA->columns['id'];
        $this->strikingAssignmentMinusOneRecurrenceA->columns['CustomerAssignment_id'] = $this->customerAssignmentMinusOneRecurrenceA->columns['id'];
        $this->strikingAssignmentMinusOneRecurrenceA->columns['Sales_id'] = $this->sales->columns['id'];
        $this->strikingAssignmentMinusOneRecurrenceA->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        
        $this->customerAssignmentMinusTwoRecurrenceA = new EntityRecord(CustomerAssignment::class, 'MinusTwoRecurrenceA');
        $this->customerAssignmentMinusTwoRecurrenceA->columns['completedTime'] = (new DateTime('first day of -2 months'))->format('Y-m-d H:i:s');
        $this->strikingAssignmentMinusTwoRecurrenceA = new EntityRecord(StrikingAssignment::class, 'MinusTwoRecurrenceA');
        $this->strikingAssignmentMinusTwoRecurrenceA->columns['id'] = $this->customerAssignmentMinusTwoRecurrenceA->columns['id'];
        $this->strikingAssignmentMinusTwoRecurrenceA->columns['CustomerAssignment_id'] = $this->customerAssignmentMinusTwoRecurrenceA->columns['id'];
        $this->strikingAssignmentMinusTwoRecurrenceA->columns['Sales_id'] = $this->sales->columns['id'];
        $this->strikingAssignmentMinusTwoRecurrenceA->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        
        $this->customerAssignmentMinusTwoRecurrenceB = new EntityRecord(CustomerAssignment::class, 'MinusTwoRecurrenceB');
        $this->customerAssignmentMinusTwoRecurrenceB->columns['completedTime'] = (new DateTime('first day of -2 months'))->format('Y-m-d H:i:s');
        $this->strikingAssignmentMinusTwoRecurrenceB = new EntityRecord(StrikingAssignment::class, 'MinusTwoRecurrenceB');
        $this->strikingAssignmentMinusTwoRecurrenceB->columns['id'] = $this->customerAssignmentMinusTwoRecurrenceB->columns['id'];
        $this->strikingAssignmentMinusTwoRecurrenceB->columns['CustomerAssignment_id'] = $this->customerAssignmentMinusTwoRecurrenceB->columns['id'];
        $this->strikingAssignmentMinusTwoRecurrenceB->columns['Sales_id'] = $this->sales->columns['id'];
        $this->strikingAssignmentMinusTwoRecurrenceB->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        
        $this->customerAssignmentMinusThreeRecurrenceA = new EntityRecord(CustomerAssignment::class, 'MinusThreeRecurrenceA');
        $this->customerAssignmentMinusThreeRecurrenceA->columns['completedTime'] = (new DateTime('first day of -3 months'))->format('Y-m-d H:i:s');
        $this->strikingAssignmentMinusThreeRecurrenceA = new EntityRecord(StrikingAssignment::class, 'MinusThreeRecurrenceA');
        $this->strikingAssignmentMinusThreeRecurrenceA->columns['id'] = $this->customerAssignmentMinusThreeRecurrenceA->columns['id'];
        $this->strikingAssignmentMinusThreeRecurrenceA->columns['CustomerAssignment_id'] = $this->customerAssignmentMinusThreeRecurrenceA->columns['id'];
        $this->strikingAssignmentMinusThreeRecurrenceA->columns['Sales_id'] = $this->sales->columns['id'];
        $this->strikingAssignmentMinusThreeRecurrenceA->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        
        $this->customerAssignmentMinusThreeRecurrenceB = new EntityRecord(CustomerAssignment::class, 'MinusThreeRecurrenceB');
        $this->customerAssignmentMinusThreeRecurrenceB->columns['completedTime'] = (new DateTime('first day of -3 months'))->format('Y-m-d H:i:s');
        $this->strikingAssignmentMinusThreeRecurrenceB = new EntityRecord(StrikingAssignment::class, 'MinusThreeRecurrenceB');
        $this->strikingAssignmentMinusThreeRecurrenceB->columns['id'] = $this->customerAssignmentMinusThreeRecurrenceB->columns['id'];
        $this->strikingAssignmentMinusThreeRecurrenceB->columns['CustomerAssignment_id'] = $this->customerAssignmentMinusThreeRecurrenceB->columns['id'];
        $this->strikingAssignmentMinusThreeRecurrenceB->columns['Sales_id'] = $this->sales->columns['id'];
        $this->strikingAssignmentMinusThreeRecurrenceB->columns['status'] = CustomerAssignmentStatus::COMPLETED->value;
        
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
        
        //
        $this->closingRequestOngoingRecurrenceA = new EntityRecord(ClosingRequest::class, 'OngoingRecurrenceA');
        $this->closingRequestOngoingRecurrenceA->columns['StrikingAssignment_id'] = $this->strikingAssignmentOngoingRecurrenceA->columns['id'];
        $this->closingRequestOngoingRecurrenceA->columns['createdTime'] = $this->customerAssignmentOngoingRecurrenceA->columns['completedTime'];
        $this->closingRequestOngoingRecurrenceA->columns['transactionValue'] = $this->transactionValueOngoingRecurrenceA;
        $this->closingRequestOngoingRecurrenceA->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        
        $this->closingRequestOngoingRecurrenceB = new EntityRecord(ClosingRequest::class, 'OngoingRecurrenceB');
        $this->closingRequestOngoingRecurrenceB->columns['StrikingAssignment_id'] = $this->strikingAssignmentOngoingRecurrenceB->columns['id'];
        $this->closingRequestOngoingRecurrenceB->columns['createdTime'] = $this->customerAssignmentOngoingRecurrenceB->columns['completedTime'];
        $this->closingRequestOngoingRecurrenceB->columns['transactionValue'] = $this->transactionValueOngoingRecurrenceB;
        $this->closingRequestOngoingRecurrenceB->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        
        $this->closingRequestOngoingRecurrenceC = new EntityRecord(ClosingRequest::class, 'OngoingRecurrenceC');
        $this->closingRequestOngoingRecurrenceC->columns['StrikingAssignment_id'] = $this->strikingAssignmentOngoingRecurrenceC->columns['id'];
        $this->closingRequestOngoingRecurrenceC->columns['createdTime'] = $this->customerAssignmentOngoingRecurrenceC->columns['completedTime'];
        $this->closingRequestOngoingRecurrenceC->columns['transactionValue'] = $this->transactionValueOngoingRecurrenceC;
        $this->closingRequestOngoingRecurrenceC->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        
        $this->closingRequestMinusOneRecurrenceA = new EntityRecord(ClosingRequest::class, 'MinusOneRecurrenceA');
        $this->closingRequestMinusOneRecurrenceA->columns['StrikingAssignment_id'] = $this->strikingAssignmentMinusOneRecurrenceA->columns['id'];
        $this->closingRequestMinusOneRecurrenceA->columns['createdTime'] = $this->customerAssignmentMinusOneRecurrenceA->columns['completedTime'];
        $this->closingRequestMinusOneRecurrenceA->columns['transactionValue'] = $this->transactionValueMinusOneRecurrenceA;
        $this->closingRequestMinusOneRecurrenceA->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        
        $this->closingRequestMinusTwoRecurrenceA = new EntityRecord(ClosingRequest::class, 'MinusTwoRecurrenceA');
        $this->closingRequestMinusTwoRecurrenceA->columns['StrikingAssignment_id'] = $this->strikingAssignmentMinusTwoRecurrenceA->columns['id'];
        $this->closingRequestMinusTwoRecurrenceA->columns['createdTime'] = $this->customerAssignmentMinusTwoRecurrenceA->columns['completedTime'];
        $this->closingRequestMinusTwoRecurrenceA->columns['transactionValue'] = $this->transactionValueMinusTwoRecurrenceA;
        $this->closingRequestMinusTwoRecurrenceA->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        
        $this->closingRequestMinusTwoRecurrenceB = new EntityRecord(ClosingRequest::class, 'MinusTwoRecurrenceB');
        $this->closingRequestMinusTwoRecurrenceB->columns['StrikingAssignment_id'] = $this->strikingAssignmentMinusTwoRecurrenceB->columns['id'];
        $this->closingRequestMinusTwoRecurrenceB->columns['createdTime'] = $this->customerAssignmentMinusTwoRecurrenceB->columns['completedTime'];
        $this->closingRequestMinusTwoRecurrenceB->columns['transactionValue'] = $this->transactionValueMinusTwoRecurrenceB;
        $this->closingRequestMinusTwoRecurrenceB->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        
        $this->closingRequestMinusThreeRecurrenceA = new EntityRecord(ClosingRequest::class, 'MinusThreeRecurrenceA');
        $this->closingRequestMinusThreeRecurrenceA->columns['StrikingAssignment_id'] = $this->strikingAssignmentMinusThreeRecurrenceA->columns['id'];
        $this->closingRequestMinusThreeRecurrenceA->columns['createdTime'] = $this->customerAssignmentMinusThreeRecurrenceA->columns['completedTime'];
        $this->closingRequestMinusThreeRecurrenceA->columns['transactionValue'] = $this->transactionValueMinusThreeRecurrenceA;
        $this->closingRequestMinusThreeRecurrenceA->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        
        $this->closingRequestMinusThreeRecurrenceB = new EntityRecord(ClosingRequest::class, 'MinusThreeRecurrenceB');
        $this->closingRequestMinusThreeRecurrenceB->columns['StrikingAssignment_id'] = $this->strikingAssignmentMinusThreeRecurrenceB->columns['id'];
        $this->closingRequestMinusThreeRecurrenceB->columns['createdTime'] = $this->customerAssignmentMinusThreeRecurrenceB->columns['completedTime'];
        $this->closingRequestMinusThreeRecurrenceB->columns['transactionValue'] = $this->transactionValueMinusThreeRecurrenceB;
        $this->closingRequestMinusThreeRecurrenceB->columns['status'] = ManagementApprovalStatus::APPROVED->value;
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('StrikerMetric')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('StrikingAssignment')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
        $this->connection->table('SalesActivityReport')->truncate();
        $this->connection->table('ClosingRequest')->truncate();
    }
    
    protected function viewAllStrikerMetricSummary()
    {
        $this->prepareSalesDependency();
        //
        $this->strikerMetricOne->insert($this->connection);
        $this->strikerMetricTwo->insert($this->connection);
        $this->strikerMetricThree->insert($this->connection);
        
        //
        $this->customerAssignmentOngoingRecurrenceA->insert($this->connection);
        $this->strikingAssignmentOngoingRecurrenceA->insert($this->connection);
        $this->customerAssignmentOngoingRecurrenceB->insert($this->connection);
        $this->strikingAssignmentOngoingRecurrenceB->insert($this->connection);
        $this->customerAssignmentOngoingRecurrenceC->insert($this->connection);
        $this->strikingAssignmentOngoingRecurrenceC->insert($this->connection);
        $this->customerAssignmentMinusOneRecurrenceA->insert($this->connection);
        $this->strikingAssignmentMinusOneRecurrenceA->insert($this->connection);
        $this->customerAssignmentMinusTwoRecurrenceA->insert($this->connection);
        $this->strikingAssignmentMinusTwoRecurrenceA->insert($this->connection);
        $this->customerAssignmentMinusTwoRecurrenceB->insert($this->connection);
        $this->strikingAssignmentMinusTwoRecurrenceB->insert($this->connection);
        $this->customerAssignmentMinusThreeRecurrenceA->insert($this->connection);
        $this->strikingAssignmentMinusThreeRecurrenceA->insert($this->connection);
        $this->customerAssignmentMinusThreeRecurrenceB->insert($this->connection);
        $this->strikingAssignmentMinusThreeRecurrenceB->insert($this->connection);
        
        //
        $this->customerAssignment->insert($this->connection);
        $this->strikingAssignment->insert($this->connection);
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
        $this->closingRequestOngoingRecurrenceA->insert($this->connection);
        $this->closingRequestOngoingRecurrenceB->insert($this->connection);
        $this->closingRequestOngoingRecurrenceC->insert($this->connection);
        $this->closingRequestMinusOneRecurrenceA->insert($this->connection);
        $this->closingRequestMinusTwoRecurrenceA->insert($this->connection);
        $this->closingRequestMinusTwoRecurrenceB->insert($this->connection);
        $this->closingRequestMinusThreeRecurrenceA->insert($this->connection);
        $this->closingRequestMinusThreeRecurrenceB->insert($this->connection);
        //
        $this->response = $this->get("api/view-all-striking-metric-achievement", $this->sales->token);
    }
    public function test_viewAllStrikerMetricSummary_200()
    {
$this->disableExceptionHandling();
        $this->viewAllStrikerMetricSummary();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            "dailyAchievement" => 3,
            "dailyReminderTarget" => $this->strikerMetricOne->columns['dailyReminderTarget'],
            'name' => $this->strikerMetricOne->columns['name'],
            'target' => $this->strikerMetricOne->columns['target'],
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
            "dailyReminderTarget" => $this->strikerMetricTwo->columns['dailyReminderTarget'],
            'name' => $this->strikerMetricTwo->columns['name'],
            'target' => $this->strikerMetricTwo->columns['target'],
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
        $this->seeJsonContains([
            "dailyAchievement" => strval($this->transactionValueOngoingRecurrenceA + $this->transactionValueOngoingRecurrenceB + $this->transactionValueOngoingRecurrenceC),
            "dailyReminderTarget" => $this->strikerMetricThree->columns['dailyReminderTarget'],
            'name' => $this->strikerMetricThree->columns['name'],
            'target' => $this->strikerMetricThree->columns['target'],
            'result' => [
                [
                    'evaluationTime' => (new DateTime())->format('Y-m'),
                    'achievement' => strval($this->transactionValueOngoingRecurrenceA + $this->transactionValueOngoingRecurrenceB + $this->transactionValueOngoingRecurrenceC),
                ],
                [
                    'evaluationTime' => (new DateTime('-1 months'))->format('Y-m'),
                    'achievement' => strval($this->transactionValueMinusOneRecurrenceA),
                ],
                [
                    'evaluationTime' => (new DateTime('-2 months'))->format('Y-m'),
                    'achievement' => strval($this->transactionValueMinusTwoRecurrenceA + $this->transactionValueMinusTwoRecurrenceB),
                ],
            ],
        ]);
    }
    
}
