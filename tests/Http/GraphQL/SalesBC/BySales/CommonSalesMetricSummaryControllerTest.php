<?php

namespace App\Http\Controllers\SalesBC\BySales;

use Company\Domain\Model\CommonSalesMetric;
use Company\Domain\Model\Personnel\Sales\CustomerAssignment\ClosingRequest;
use Company\Domain\Model\Personnel\Sales\CustomerAssignment\SalesActivitySchedule;
use Company\Domain\Model\Personnel\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use DateTime;
use Sales\Domain\Model\Sales\CustomerAssignment;
use SharedContext\Domain\Enum\EvaluationType;
use SharedContext\Domain\Enum\ManagementApprovalStatus;
use SharedContext\Domain\Enum\RecurrenceType;
use SharedContext\Domain\Enum\SalesMetricType;
use Tests\Http\GraphQL\SalesBC\SalesBCTestCase;
use Tests\Http\Record\EntityRecord;

class CommonSalesMetricSummaryControllerTest extends SalesBCTestCase
{
    protected EntityRecord $commonSalesMetricOne;
    protected EntityRecord $commonSalesMetricTwo;
    protected EntityRecord $commonSalesMetricThree;
    
    protected EntityRecord $customerAssignment;
    protected EntityRecord $salesActivitySchedule;
    
    protected EntityRecord $closingRequestOngoingRecurrenceA;
    protected EntityRecord $closingRequestOngoingRecurrenceB;
    protected EntityRecord $closingRequestOngoingRecurrenceC;
    protected EntityRecord $closingRequestMinusOneRecurrenceA;
    protected EntityRecord $closingRequestMinusOneRecurrenceB;
    protected EntityRecord $closingRequestMinusTwoRecurrenceA;
    protected EntityRecord $closingRequestMinusTwoRecurrenceB;
    protected EntityRecord $closingRequestMinusThreeRecurrenceA;
    protected EntityRecord $closingRequestMinusThreeRecurrenceB;
    
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
        $this->connection->table('CommonSalesMetric')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
        $this->connection->table('SalesActivityReport')->truncate();
        $this->connection->table('ClosingRequest')->truncate();
        //
        $this->commonSalesMetricOne = new EntityRecord(CommonSalesMetric::class, 1);
        $this->commonSalesMetricOne->columns['target'] = 111;
        $this->commonSalesMetricOne->columns['metricType'] = SalesMetricType::APPROVED_CLOSING_REQUEST->value;
        $this->commonSalesMetricOne->columns['evaluationType'] = EvaluationType::SUM->value;
        $this->commonSalesMetricOne->columns['recurrenceType'] = RecurrenceType::MONTHLY->value;
        $this->commonSalesMetricOne->columns['recurrenceCount'] = 3;
        $this->commonSalesMetricTwo = new EntityRecord(CommonSalesMetric::class, 2);
        $this->commonSalesMetricTwo->columns['target'] = 222;
        $this->commonSalesMetricTwo->columns['metricType'] = SalesMetricType::SALES_ACTIVITY_REPORT->value;
        $this->commonSalesMetricTwo->columns['evaluationType'] = EvaluationType::COUNT->value;
        $this->commonSalesMetricTwo->columns['recurrenceType'] = RecurrenceType::MONTHLY->value;
        $this->commonSalesMetricTwo->columns['recurrenceCount'] = 3;
        $this->commonSalesMetricThree = new EntityRecord(CommonSalesMetric::class, 3);
        $this->commonSalesMetricThree->columns['target'] = 333;
        $this->commonSalesMetricThree->columns['metricType'] = SalesMetricType::APPROVED_CLOSING_REQUEST->value;
        $this->commonSalesMetricThree->columns['evaluationType'] = EvaluationType::COUNT->value;
        $this->commonSalesMetricThree->columns['recurrenceType'] = RecurrenceType::MONTHLY->value;
        $this->commonSalesMetricThree->columns['recurrenceCount'] = 3;
        
        $this->customerAssignment = new EntityRecord(CustomerAssignment::class, 'main');
        $this->customerAssignment->columns['Sales_id'] = $this->sales->columns['id'];
        
        $this->salesActivitySchedule = new EntityRecord(SalesActivitySchedule::class, 'main');
        $this->salesActivitySchedule->columns['CustomerAssignment_id'] = $this->customerAssignment->columns['id'];
        
        $this->closingRequestOngoingRecurrenceA = new EntityRecord(ClosingRequest::class, 'ongoingRecurrenceA');
        $this->closingRequestOngoingRecurrenceA->columns['CustomerAssignment_id'] = $this->customerAssignment->columns['id'];
        $this->closingRequestOngoingRecurrenceA->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestOngoingRecurrenceA->columns['createdTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->closingRequestOngoingRecurrenceA->columns['transactionValue'] = 101;
        $this->closingRequestOngoingRecurrenceB = new EntityRecord(ClosingRequest::class, 'ongoingRecurrenceB');
        $this->closingRequestOngoingRecurrenceB->columns['CustomerAssignment_id'] = $this->customerAssignment->columns['id'];
        $this->closingRequestOngoingRecurrenceB->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestOngoingRecurrenceB->columns['createdTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->closingRequestOngoingRecurrenceB->columns['transactionValue'] = 102;
        $this->closingRequestOngoingRecurrenceC = new EntityRecord(ClosingRequest::class, 'ongoingRecurrenceC');
        $this->closingRequestOngoingRecurrenceC->columns['CustomerAssignment_id'] = $this->customerAssignment->columns['id'];
        $this->closingRequestOngoingRecurrenceC->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestOngoingRecurrenceC->columns['createdTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->closingRequestOngoingRecurrenceC->columns['transactionValue'] = 103;
        $this->closingRequestMinusOneRecurrenceA = new EntityRecord(ClosingRequest::class, 'minusOneRecurrenceA');
        $this->closingRequestMinusOneRecurrenceA->columns['CustomerAssignment_id'] = $this->customerAssignment->columns['id'];
        $this->closingRequestMinusOneRecurrenceA->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMinusOneRecurrenceA->columns['createdTime'] = (new DateTime('-1 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMinusOneRecurrenceA->columns['transactionValue'] = 111;
        $this->closingRequestMinusOneRecurrenceB = new EntityRecord(ClosingRequest::class, 'minusOneRecurrenceB');
        $this->closingRequestMinusOneRecurrenceB->columns['CustomerAssignment_id'] = $this->customerAssignment->columns['id'];
        $this->closingRequestMinusOneRecurrenceB->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMinusOneRecurrenceB->columns['createdTime'] = (new DateTime('-1 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMinusOneRecurrenceB->columns['transactionValue'] = 112;
        $this->closingRequestMinusTwoRecurrenceA = new EntityRecord(ClosingRequest::class, 'minusTwoRecurrenceA');
        $this->closingRequestMinusTwoRecurrenceA->columns['CustomerAssignment_id'] = $this->customerAssignment->columns['id'];
        $this->closingRequestMinusTwoRecurrenceA->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMinusTwoRecurrenceA->columns['createdTime'] = (new DateTime('-2 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMinusTwoRecurrenceA->columns['transactionValue'] = 121;
        $this->closingRequestMinusTwoRecurrenceB = new EntityRecord(ClosingRequest::class, 'minusTwoRecurrenceB');
        $this->closingRequestMinusTwoRecurrenceB->columns['CustomerAssignment_id'] = $this->customerAssignment->columns['id'];
        $this->closingRequestMinusTwoRecurrenceB->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMinusTwoRecurrenceB->columns['createdTime'] = (new DateTime('-2 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMinusTwoRecurrenceB->columns['transactionValue'] = 122;
        $this->closingRequestMinusThreeRecurrenceA = new EntityRecord(ClosingRequest::class, 'minusThreeRecurrenceA');
        $this->closingRequestMinusThreeRecurrenceA->columns['CustomerAssignment_id'] = $this->customerAssignment->columns['id'];
        $this->closingRequestMinusThreeRecurrenceA->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMinusThreeRecurrenceA->columns['createdTime'] = (new DateTime('-3 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMinusThreeRecurrenceA->columns['transactionValue'] = 131;
        $this->closingRequestMinusThreeRecurrenceB = new EntityRecord(ClosingRequest::class, 'minusThreeRecurrenceB');
        $this->closingRequestMinusThreeRecurrenceB->columns['CustomerAssignment_id'] = $this->customerAssignment->columns['id'];
        $this->closingRequestMinusThreeRecurrenceB->columns['createdTime'] = (new DateTime('-3 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMinusThreeRecurrenceB->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMinusThreeRecurrenceB->columns['transactionValue'] = 132;
        
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
        $this->salesActivityReportMinusOneRecurrenceA->columns['submitTime'] = (new DateTime('-1 months'))->format('Y-m-d H:i:s');
        $this->salesActivityReportMinusOneRecurrenceB = new EntityRecord(SalesActivityReport::class, 'minusOneRecurrenceB');
        $this->salesActivityReportMinusOneRecurrenceB->columns['SalesActivitySchedule_id'] = $this->salesActivitySchedule->columns['id'];
        $this->salesActivityReportMinusOneRecurrenceB->columns['submitTime'] = (new DateTime('-1 months'))->format('Y-m-d H:i:s');
        $this->salesActivityReportMinusTwoRecurrenceA = new EntityRecord(SalesActivityReport::class, 'minusTwoRecurrenceA');
        $this->salesActivityReportMinusTwoRecurrenceA->columns['SalesActivitySchedule_id'] = $this->salesActivitySchedule->columns['id'];
        $this->salesActivityReportMinusTwoRecurrenceA->columns['submitTime'] = (new DateTime('-2 months'))->format('Y-m-d H:i:s');
        $this->salesActivityReportMinusTwoRecurrenceB = new EntityRecord(SalesActivityReport::class, 'minusTwoRecurrenceB');
        $this->salesActivityReportMinusTwoRecurrenceB->columns['SalesActivitySchedule_id'] = $this->salesActivitySchedule->columns['id'];
        $this->salesActivityReportMinusTwoRecurrenceB->columns['submitTime'] = (new DateTime('-2 months'))->format('Y-m-d H:i:s');
        $this->salesActivityReportMinusThreeRecurrenceA = new EntityRecord(SalesActivityReport::class, 'minusThreeRecurrenceA');
        $this->salesActivityReportMinusThreeRecurrenceA->columns['SalesActivitySchedule_id'] = $this->salesActivitySchedule->columns['id'];
        $this->salesActivityReportMinusThreeRecurrenceA->columns['submitTime'] = (new DateTime('-3 months'))->format('Y-m-d H:i:s');
        $this->salesActivityReportMinusThreeRecurrenceB = new EntityRecord(SalesActivityReport::class, 'minusThreeRecurrenceB');
        $this->salesActivityReportMinusThreeRecurrenceB->columns['SalesActivitySchedule_id'] = $this->salesActivitySchedule->columns['id'];
        $this->salesActivityReportMinusThreeRecurrenceB->columns['submitTime'] = (new DateTime('-3 months'))->format('Y-m-d H:i:s');
    }
    
    protected function tearDown(): void
    {
//        parent::tearDown();
//        $this->connection->table('CommonSalesMetric')->truncate();
//        $this->connection->table('CustomerAssignment')->truncate();
//        $this->connection->table('SalesActivitySchedule')->truncate();
//        $this->connection->table('SalesActivityReport')->truncate();
//        $this->connection->table('ClosingRequest')->truncate();
    }
    
    protected function viewAllCommonSalesMetricSummary()
    {
        $this->prepareSalesDependency();
        //
        $this->commonSalesMetricOne->insert($this->connection);
        $this->commonSalesMetricTwo->insert($this->connection);
        $this->commonSalesMetricThree->insert($this->connection);
        
        $this->customerAssignment->insert($this->connection);
        $this->salesActivitySchedule->insert($this->connection);
        
        $this->closingRequestOngoingRecurrenceA->insert($this->connection);
        $this->closingRequestOngoingRecurrenceB->insert($this->connection);
        $this->closingRequestOngoingRecurrenceC->insert($this->connection);
        $this->closingRequestMinusOneRecurrenceA->insert($this->connection);
        $this->closingRequestMinusOneRecurrenceB->insert($this->connection);
        $this->closingRequestMinusTwoRecurrenceA->insert($this->connection);
        $this->closingRequestMinusTwoRecurrenceB->insert($this->connection);
        $this->closingRequestMinusThreeRecurrenceA->insert($this->connection);
        $this->closingRequestMinusThreeRecurrenceB->insert($this->connection);
        
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
//        $this->get("api/sales/{$this->sales->columns['id']}/view-all-common-sales-metric-summary", $this->personnel->token);
        $this->response = $this->get("api/sales/{$this->sales->columns['id']}/view-all-common-sales-metric-summary", $this->personnel->token);
    }
    public function test_viewAllCommonSalesMetricSummary_200()
    {
$this->disableExceptionHandling();
        $this->viewAllCommonSalesMetricSummary();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'name' => $this->commonSalesMetricOne->columns['name'],
            'target' => $this->commonSalesMetricOne->columns['target'],
            'result' => [
                [
                    'time' => (new DateTime())->format('Y-m'),
                    'value' => strval(101 + 102 + 103),
                ],
                [
                    'time' => (new DateTime('-1 months'))->format('Y-m'),
                    'value' => strval(111 + 112),
                ],
                [
                    'time' => (new DateTime('-2 months'))->format('Y-m'),
                    'value' => strval(121 + 122),
                ],
            ],
        ]);
        $this->seeJsonContains([
            'name' => $this->commonSalesMetricTwo->columns['name'],
            'target' => $this->commonSalesMetricTwo->columns['target'],
            'result' => [
                [
                    'time' => (new DateTime())->format('Y-m'),
                    'value' => 3,
                ],
                [
                    'time' => (new DateTime('-1 months'))->format('Y-m'),
                    'value' => 2,
                ],
                [
                    'time' => (new DateTime('-2 months'))->format('Y-m'),
                    'value' => 2,
                ],
            ],
        ]);
        $this->seeJsonContains([
            'name' => $this->commonSalesMetricThree->columns['name'],
            'target' => $this->commonSalesMetricThree->columns['target'],
            'result' => [
                [
                    'time' => (new DateTime())->format('Y-m'),
                    'value' => 3,
                ],
                [
                    'time' => (new DateTime('-1 months'))->format('Y-m'),
                    'value' => 2,
                ],
                [
                    'time' => (new DateTime('-2 months'))->format('Y-m'),
                    'value' => 2,
                ],
            ],
        ]);
    }
    
}
