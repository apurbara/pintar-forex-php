<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\CompanyMetric;
use Company\Domain\Model\Manager\Sales;
use Company\Domain\Model\Manager\Sales\CustomerAssignment;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\ClosingRequest;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\SalesActivitySchedule;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Company\Domain\Model\SalesPerformanceMetric;
use Company\Domain\Model\SalesPerformanceMetric\SalesPerformanceMetricEvaluation;
use Company\Domain\Model\SalesRank;
use DateTime;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Shared\Domain\Enum\MetricType;
use Shared\Domain\Enum\QueryOrder;
use Shared\Domain\Enum\RecurrenceType;
use Shared\Domain\Enum\SalesPerformanceMetricType;
use Tests\Company\Application\Controllers\CompanyControllerTestCase;
use Tests\resources\Application\EntityRecord;

class PerformanceSummaryControllerTest extends CompanyControllerTestCase
{
    protected EntityRecord $companyMetricOne;
    protected EntityRecord $companyMetricTwo;
    
    protected EntityRecord $salesRankOne;
    protected EntityRecord $salesRankTwo;
    
    protected EntityRecord $salesPerformanceMetricOne;
    protected EntityRecord $salesPerformanceMetricTwo;
    
    protected EntityRecord $salesPerformanceMetricEvaluationOneA;
    protected EntityRecord $salesPerformanceMetricEvaluationOneB;
    protected EntityRecord $salesPerformanceMetricEvaluationOneC;
    protected EntityRecord $salesPerformanceMetricEvaluationTwoA;
    
    protected EntityRecord $salesOne;
    protected EntityRecord $salesTwo;
    protected EntityRecord $salesThree;
    
    protected EntityRecord $customerAssignmentOneA;
    protected EntityRecord $customerAssignmentTwoA;
    protected EntityRecord $customerAssignmentThreeA;
    
    protected EntityRecord $salesActivityScheduleOneAOne;
    protected EntityRecord $salesActivityScheduleTwoAOne;
    protected EntityRecord $salesActivityScheduleThreeAOne;
    
    protected EntityRecord $salesActivityReportOngoingEvaluationTimeOneAOneA;
    protected EntityRecord $salesActivityReportOngoingEvaluationTimeOneAOneB;
    protected EntityRecord $salesActivityReportOngoingEvaluationTimeOneAOneC;
    protected EntityRecord $salesActivityReportOngoingEvaluationTimeTwoAOneA;
    protected EntityRecord $salesActivityReportOngoingEvaluationTimeThreeAOneA;
    protected EntityRecord $salesActivityReportMinusOneEvaluationTimeOneAOneA;
    protected EntityRecord $salesActivityReportMinusOneEvaluationTimeOneAOneB;
    protected EntityRecord $salesActivityReportMinusOneEvaluationTimeTwoAOneA;
    protected EntityRecord $salesActivityReportMinusOneEvaluationTimeThreeAOneA;
    protected EntityRecord $salesActivityReportMinusTwoEvaluationTimeOneAOneA;
    protected EntityRecord $salesActivityReportMinusTwoEvaluationTimeOneAOneB;
    protected EntityRecord $salesActivityReportMinusTwoEvaluationTimeTwoAOneA;
    protected EntityRecord $salesActivityReportMinusTwoEvaluationTimeThreeAOneA;
    protected EntityRecord $salesActivityReportMinusThreeEvaluationTimeOneAOneA;
    protected EntityRecord $salesActivityReportMinusThreeEvaluationTimeOneAOneB;
    protected EntityRecord $salesActivityReportMinusThreeEvaluationTimeTwoAOneA;
    protected EntityRecord $salesActivityReportMinusThreeEvaluationTimeThreeAOneA;
    
    protected EntityRecord $closingRequestOngoingEvaluationTimeOneAOneA;
    protected EntityRecord $closingRequestOngoingEvaluationTimeOneAOneB;
    protected EntityRecord $closingRequestOngoingEvaluationTimeOneAOneC;
    protected EntityRecord $closingRequestOngoingEvaluationTimeTwoAOneA;
    protected EntityRecord $closingRequestOngoingEvaluationTimeThreeAOneA;
    protected EntityRecord $closingRequestMinusOneEvaluationTimeOneAOneA;
    protected EntityRecord $closingRequestMinusOneEvaluationTimeOneAOneB;
    protected EntityRecord $closingRequestMinusOneEvaluationTimeTwoAOneA;
    protected EntityRecord $closingRequestMinusOneEvaluationTimeThreeAOneA;
    protected EntityRecord $closingRequestMinusTwoEvaluationTimeOneAOneA;
    protected EntityRecord $closingRequestMinusTwoEvaluationTimeOneAOneB;
    protected EntityRecord $closingRequestMinusTwoEvaluationTimeTwoAOneA;
    protected EntityRecord $closingRequestMinusTwoEvaluationTimeThreeAOneA;
    protected EntityRecord $closingRequestMinusThreeEvaluationTimeOneAOneA;
    protected EntityRecord $closingRequestMinusThreeEvaluationTimeOneAOneB;
    protected EntityRecord $closingRequestMinusThreeEvaluationTimeTwoAOneA;
    protected EntityRecord $closingRequestMinusThreeEvaluationTimeThreeAOneA;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('CompanyMetric')->truncate();
        $this->connection->table('SalesRank')->truncate();
        $this->connection->table('SalesPerformanceMetric')->truncate();
        $this->connection->table('SalesPerformanceMetricEvaluation')->truncate();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('SalesActivitySchedule')->truncate();
        $this->connection->table('SalesActivityReport')->truncate();
        $this->connection->table('ClosingRequest')->truncate();
        //
        $this->companyMetricOne = new EntityRecord(CompanyMetric::class, 1);
        $this->companyMetricOne->columns['target'] = 100;
        $this->companyMetricOne->columns['metricType'] = MetricType::APPROVED_CLOSING_REQUEST->value;
        $this->companyMetricOne->columns['evaluationType'] = EvaluationType::SUM->value;
        $this->companyMetricOne->columns['recurrenceType'] = RecurrenceType::MONTHLY->value;
        $this->companyMetricOne->columns['recurrenceCount'] = 3;
        $this->companyMetricTwo = new EntityRecord(CompanyMetric::class, 2);
        $this->companyMetricTwo->columns['target'] = 200;
        $this->companyMetricTwo->columns['metricType'] = MetricType::SALES_ACTIVITY_REPORT->value;
        $this->companyMetricTwo->columns['evaluationType'] = EvaluationType::COUNT->value;
        $this->companyMetricTwo->columns['recurrenceType'] = RecurrenceType::MONTHLY->value;
        $this->companyMetricTwo->columns['recurrenceCount'] = 3;
        
        $this->salesRankOne = new EntityRecord(SalesRank::class, 1);
        $this->salesRankOne->columns['name'] = 'top closing value achiever';
        $this->salesRankOne->columns['metricType'] = MetricType::APPROVED_CLOSING_REQUEST->value;
        $this->salesRankOne->columns['evaluationType'] = EvaluationType::SUM->value;
        $this->salesRankOne->columns['recurrenceType'] = RecurrenceType::MONTHLY->value;
        $this->salesRankOne->columns['queryOrder'] = QueryOrder::DESC->value;
        $this->salesRankOne->columns['displaySalesNumber'] = 2;
        $this->salesRankTwo = new EntityRecord(SalesRank::class, 2);
        $this->salesRankTwo->columns['name'] = 'least active';
        $this->salesRankTwo->columns['metricType'] = MetricType::SALES_ACTIVITY_REPORT->value;
        $this->salesRankTwo->columns['evaluationType'] = EvaluationType::COUNT->value;
        $this->salesRankTwo->columns['recurrenceType'] = RecurrenceType::MONTHLY->value;
        $this->salesRankTwo->columns['queryOrder'] = QueryOrder::ASC->value;
        $this->salesRankTwo->columns['displaySalesNumber'] = 2;
        
        $this->salesPerformanceMetricOne = new EntityRecord(SalesPerformanceMetric::class, 1);
        $this->salesPerformanceMetricOne->columns['metricType'] = SalesPerformanceMetricType::APPROVED_CLOSING_REQUEST_SUM->value;
        $this->salesPerformanceMetricOne->columns['recurrenceType'] = RecurrenceType::MONTHLY->value;
        $this->salesPerformanceMetricOne->columns['recurrenceCount'] = 3;
        $this->salesPerformanceMetricTwo = new EntityRecord(SalesPerformanceMetric::class, 2);
        $this->salesPerformanceMetricTwo->columns['metricType'] = SalesPerformanceMetricType::SALES_ACTIVITY_REPORT->value;
        $this->salesPerformanceMetricTwo->columns['recurrenceType'] = RecurrenceType::MONTHLY->value;
        $this->salesPerformanceMetricTwo->columns['recurrenceCount'] = 3;
        
        $this->salesPerformanceMetricEvaluationOneA = new EntityRecord(SalesPerformanceMetricEvaluation::class, 'OneA');
        $this->salesPerformanceMetricEvaluationOneA->columns['SalesPerformanceMetric_id'] = $this->salesPerformanceMetricOne->columns['id'];
        $this->salesPerformanceMetricEvaluationOneA->columns['evaluationType'] = EvaluationType::MAX->value;
        $this->salesPerformanceMetricEvaluationOneA->columns['alias'] = 'maxTransaction';
        $this->salesPerformanceMetricEvaluationOneB = new EntityRecord(SalesPerformanceMetricEvaluation::class, 'OneB');
        $this->salesPerformanceMetricEvaluationOneB->columns['SalesPerformanceMetric_id'] = $this->salesPerformanceMetricOne->columns['id'];
        $this->salesPerformanceMetricEvaluationOneB->columns['evaluationType'] = EvaluationType::MIN->value;
        $this->salesPerformanceMetricEvaluationOneB->columns['alias'] = 'minTransaction';
        $this->salesPerformanceMetricEvaluationOneC = new EntityRecord(SalesPerformanceMetricEvaluation::class, 'OneC');
        $this->salesPerformanceMetricEvaluationOneC->columns['SalesPerformanceMetric_id'] = $this->salesPerformanceMetricOne->columns['id'];
        $this->salesPerformanceMetricEvaluationOneC->columns['evaluationType'] = EvaluationType::AVG->value;
        $this->salesPerformanceMetricEvaluationOneC->columns['alias'] = 'avgTransaction';
        $this->salesPerformanceMetricEvaluationTwoA = new EntityRecord(SalesPerformanceMetricEvaluation::class, 'TwoA');
        $this->salesPerformanceMetricEvaluationTwoA->columns['SalesPerformanceMetric_id'] = $this->salesPerformanceMetricTwo->columns['id'];
        $this->salesPerformanceMetricEvaluationTwoA->columns['evaluationType'] = EvaluationType::AVG->value;
        $this->salesPerformanceMetricEvaluationTwoA->columns['alias'] = 'avgActivity';
        
        $this->salesOne = new EntityRecord(Sales::class, 1);
        $this->salesOne->columns['createdTime'] = (new DateTime('-1 years'))->format('Y-m-d H:i:s');
        $this->salesOne->columns['contractTerminatedTime'] = null;
        $this->salesTwo = new EntityRecord(Sales::class, 2);
        $this->salesTwo->columns['createdTime'] = (new DateTime('-1 years'))->format('Y-m-d H:i:s');
        $this->salesTwo->columns['contractTerminatedTime'] = null;
        $this->salesThree = new EntityRecord(Sales::class, 3);
        $this->salesThree->columns['createdTime'] = (new DateTime('-1 years'))->format('Y-m-d H:i:s');
        $this->salesThree->columns['contractTerminatedTime'] = null;
        
        $this->customerAssignmentOneA = new EntityRecord(CustomerAssignment::class, 'OneA');
        $this->customerAssignmentOneA->columns['Sales_id'] = $this->salesOne->columns['id'];
        $this->customerAssignmentTwoA = new EntityRecord(CustomerAssignment::class, 'TwoA');
        $this->customerAssignmentTwoA->columns['Sales_id'] = $this->salesTwo->columns['id'];
        $this->customerAssignmentThreeA = new EntityRecord(CustomerAssignment::class, 'ThreeA');
        $this->customerAssignmentThreeA->columns['Sales_id'] = $this->salesThree->columns['id'];
        
        $this->salesActivityScheduleOneAOne = new EntityRecord(SalesActivitySchedule::class, 'OneAOne');
        $this->salesActivityScheduleOneAOne->columns['CustomerAssignment_id'] = $this->customerAssignmentOneA->columns['id'];
        $this->salesActivityScheduleTwoAOne = new EntityRecord(SalesActivitySchedule::class, 'TwoAOne');
        $this->salesActivityScheduleTwoAOne->columns['CustomerAssignment_id'] = $this->customerAssignmentTwoA->columns['id'];
        $this->salesActivityScheduleThreeAOne = new EntityRecord(SalesActivitySchedule::class, 'ThreeAOne');
        $this->salesActivityScheduleThreeAOne->columns['CustomerAssignment_id'] = $this->customerAssignmentThreeA->columns['id'];
        
        $this->salesActivityReportOngoingEvaluationTimeOneAOneA = new EntityRecord(SalesActivityReport::class, 'OngoingEvaluationTimeOneAOneA');
        $this->salesActivityReportOngoingEvaluationTimeOneAOneA->columns['SalesActivitySchedule_id'] = $this->salesActivityScheduleOneAOne->columns['id'];
        $this->salesActivityReportOngoingEvaluationTimeOneAOneA->columns['submitTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->salesActivityReportOngoingEvaluationTimeOneAOneB = new EntityRecord(SalesActivityReport::class, 'OngoingEvaluationTimeOneAOneB');
        $this->salesActivityReportOngoingEvaluationTimeOneAOneB->columns['SalesActivitySchedule_id'] = $this->salesActivityScheduleOneAOne->columns['id'];
        $this->salesActivityReportOngoingEvaluationTimeOneAOneB->columns['submitTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->salesActivityReportOngoingEvaluationTimeOneAOneC = new EntityRecord(SalesActivityReport::class, 'OngoingEvaluationTimeOneAOneC');
        $this->salesActivityReportOngoingEvaluationTimeOneAOneC->columns['SalesActivitySchedule_id'] = $this->salesActivityScheduleOneAOne->columns['id'];
        $this->salesActivityReportOngoingEvaluationTimeOneAOneC->columns['submitTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->salesActivityReportOngoingEvaluationTimeTwoAOneA = new EntityRecord(SalesActivityReport::class, 'OngoingEvaluationTimeTwoAOneA');
        $this->salesActivityReportOngoingEvaluationTimeTwoAOneA->columns['SalesActivitySchedule_id'] = $this->salesActivityScheduleTwoAOne->columns['id'];
        $this->salesActivityReportOngoingEvaluationTimeTwoAOneA->columns['submitTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->salesActivityReportOngoingEvaluationTimeThreeAOneA = new EntityRecord(SalesActivityReport::class, 'OngoingEvaluationTimeThreeAOneA');
        $this->salesActivityReportOngoingEvaluationTimeThreeAOneA->columns['SalesActivitySchedule_id'] = $this->salesActivityScheduleThreeAOne->columns['id'];
        $this->salesActivityReportOngoingEvaluationTimeThreeAOneA->columns['submitTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->salesActivityReportMinusOneEvaluationTimeOneAOneA = new EntityRecord(SalesActivityReport::class, 'MinusOneEvaluationTimeOneAOneA');
        $this->salesActivityReportMinusOneEvaluationTimeOneAOneA->columns['SalesActivitySchedule_id'] = $this->salesActivityScheduleOneAOne->columns['id'];
        $this->salesActivityReportMinusOneEvaluationTimeOneAOneA->columns['submitTime'] = (new DateTime('first day of -1 month'))->format('Y-m-d H:i:s');
        $this->salesActivityReportMinusOneEvaluationTimeOneAOneB = new EntityRecord(SalesActivityReport::class, 'MinusOneEvaluationTimeOneAOneB');
        $this->salesActivityReportMinusOneEvaluationTimeOneAOneB->columns['SalesActivitySchedule_id'] = $this->salesActivityScheduleOneAOne->columns['id'];
        $this->salesActivityReportMinusOneEvaluationTimeOneAOneB->columns['submitTime'] = (new DateTime('first day of -1 month'))->format('Y-m-d H:i:s');
        $this->salesActivityReportMinusOneEvaluationTimeTwoAOneA = new EntityRecord(SalesActivityReport::class, 'MinusOneEvaluationTimeTwoAOneA');
        $this->salesActivityReportMinusOneEvaluationTimeTwoAOneA->columns['SalesActivitySchedule_id'] = $this->salesActivityScheduleTwoAOne->columns['id'];
        $this->salesActivityReportMinusOneEvaluationTimeTwoAOneA->columns['submitTime'] = (new DateTime('first day of -1 month'))->format('Y-m-d H:i:s');
        $this->salesActivityReportMinusOneEvaluationTimeThreeAOneA = new EntityRecord(SalesActivityReport::class, 'MinusOneEvaluationTimeThreeAOneA');
        $this->salesActivityReportMinusOneEvaluationTimeThreeAOneA->columns['SalesActivitySchedule_id'] = $this->salesActivityScheduleThreeAOne->columns['id'];
        $this->salesActivityReportMinusOneEvaluationTimeThreeAOneA->columns['submitTime'] = (new DateTime('first day of -1 month'))->format('Y-m-d H:i:s');
        $this->salesActivityReportMinusTwoEvaluationTimeOneAOneA = new EntityRecord(SalesActivityReport::class, 'MinusTwoEvaluationTimeOneAOneA');
        $this->salesActivityReportMinusTwoEvaluationTimeOneAOneA->columns['SalesActivitySchedule_id'] = $this->salesActivityScheduleOneAOne->columns['id'];
        $this->salesActivityReportMinusTwoEvaluationTimeOneAOneA->columns['submitTime'] = (new DateTime('first day of -2 month'))->format('Y-m-d H:i:s');
        $this->salesActivityReportMinusTwoEvaluationTimeOneAOneB = new EntityRecord(SalesActivityReport::class, 'MinusTwoEvaluationTimeOneAOneB');
        $this->salesActivityReportMinusTwoEvaluationTimeOneAOneB->columns['SalesActivitySchedule_id'] = $this->salesActivityScheduleOneAOne->columns['id'];
        $this->salesActivityReportMinusTwoEvaluationTimeOneAOneB->columns['submitTime'] = (new DateTime('first day of -2 month'))->format('Y-m-d H:i:s');
        $this->salesActivityReportMinusTwoEvaluationTimeTwoAOneA = new EntityRecord(SalesActivityReport::class, 'MinusTwoEvaluationTimeTwoAOneA');
        $this->salesActivityReportMinusTwoEvaluationTimeTwoAOneA->columns['SalesActivitySchedule_id'] = $this->salesActivityScheduleTwoAOne->columns['id'];
        $this->salesActivityReportMinusTwoEvaluationTimeTwoAOneA->columns['submitTime'] = (new DateTime('first day of -2 month'))->format('Y-m-d H:i:s');
        $this->salesActivityReportMinusTwoEvaluationTimeThreeAOneA = new EntityRecord(SalesActivityReport::class, 'MinusTwoEvaluationTimeThreeAOneA');
        $this->salesActivityReportMinusTwoEvaluationTimeThreeAOneA->columns['SalesActivitySchedule_id'] = $this->salesActivityScheduleThreeAOne->columns['id'];
        $this->salesActivityReportMinusTwoEvaluationTimeThreeAOneA->columns['submitTime'] = (new DateTime('first day of -2 month'))->format('Y-m-d H:i:s');
        $this->salesActivityReportMinusThreeEvaluationTimeOneAOneA = new EntityRecord(SalesActivityReport::class, 'MinusThreeEvaluationTimeOneAOneA');
        $this->salesActivityReportMinusThreeEvaluationTimeOneAOneA->columns['SalesActivitySchedule_id'] = $this->salesActivityScheduleOneAOne->columns['id'];
        $this->salesActivityReportMinusThreeEvaluationTimeOneAOneA->columns['submitTime'] = (new DateTime('first day of -3 month'))->format('Y-m-d H:i:s');
        $this->salesActivityReportMinusThreeEvaluationTimeOneAOneB = new EntityRecord(SalesActivityReport::class, 'MinusThreeEvaluationTimeOneAOneB');
        $this->salesActivityReportMinusThreeEvaluationTimeOneAOneB->columns['SalesActivitySchedule_id'] = $this->salesActivityScheduleOneAOne->columns['id'];
        $this->salesActivityReportMinusThreeEvaluationTimeOneAOneB->columns['submitTime'] = (new DateTime('first day of -3 month'))->format('Y-m-d H:i:s');
        $this->salesActivityReportMinusThreeEvaluationTimeTwoAOneA = new EntityRecord(SalesActivityReport::class, 'MinusThreeEvaluationTimeTwoAOneA');
        $this->salesActivityReportMinusThreeEvaluationTimeTwoAOneA->columns['SalesActivitySchedule_id'] = $this->salesActivityScheduleTwoAOne->columns['id'];
        $this->salesActivityReportMinusThreeEvaluationTimeTwoAOneA->columns['submitTime'] = (new DateTime('first day of -3 month'))->format('Y-m-d H:i:s');
        $this->salesActivityReportMinusThreeEvaluationTimeThreeAOneA = new EntityRecord(SalesActivityReport::class, 'MinusThreeEvaluationTimeThreeAOneA');
        $this->salesActivityReportMinusThreeEvaluationTimeThreeAOneA->columns['SalesActivitySchedule_id'] = $this->salesActivityScheduleThreeAOne->columns['id'];
        $this->salesActivityReportMinusThreeEvaluationTimeThreeAOneA->columns['submitTime'] = (new DateTime('first day of -3 month'))->format('Y-m-d H:i:s');
        
        $this->closingRequestOngoingEvaluationTimeOneAOneA = new EntityRecord(ClosingRequest::class, 'OngoingEvaluationTimeOneAOneA');
        $this->closingRequestOngoingEvaluationTimeOneAOneA->columns['CustomerAssignment_id'] = $this->customerAssignmentOneA->columns['id'];
        $this->closingRequestOngoingEvaluationTimeOneAOneA->columns['createdTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->closingRequestOngoingEvaluationTimeOneAOneA->columns['transactionValue'] = 101;
        $this->closingRequestOngoingEvaluationTimeOneAOneA->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestOngoingEvaluationTimeOneAOneB = new EntityRecord(ClosingRequest::class, 'OngoingEvaluationTimeOneAOneB');
        $this->closingRequestOngoingEvaluationTimeOneAOneB->columns['CustomerAssignment_id'] = $this->customerAssignmentOneA->columns['id'];
        $this->closingRequestOngoingEvaluationTimeOneAOneB->columns['createdTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->closingRequestOngoingEvaluationTimeOneAOneB->columns['transactionValue'] = 102;
        $this->closingRequestOngoingEvaluationTimeOneAOneB->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestOngoingEvaluationTimeOneAOneC = new EntityRecord(ClosingRequest::class, 'OngoingEvaluationTimeOneAOneC');
        $this->closingRequestOngoingEvaluationTimeOneAOneC->columns['CustomerAssignment_id'] = $this->customerAssignmentOneA->columns['id'];
        $this->closingRequestOngoingEvaluationTimeOneAOneC->columns['createdTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->closingRequestOngoingEvaluationTimeOneAOneC->columns['transactionValue'] = 103;
        $this->closingRequestOngoingEvaluationTimeOneAOneC->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestOngoingEvaluationTimeTwoAOneA = new EntityRecord(ClosingRequest::class, 'OngoingEvaluationTimeTwoAOneA');
        $this->closingRequestOngoingEvaluationTimeTwoAOneA->columns['CustomerAssignment_id'] = $this->customerAssignmentTwoA->columns['id'];
        $this->closingRequestOngoingEvaluationTimeTwoAOneA->columns['createdTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->closingRequestOngoingEvaluationTimeTwoAOneA->columns['transactionValue'] = 201;
        $this->closingRequestOngoingEvaluationTimeTwoAOneA->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestOngoingEvaluationTimeThreeAOneA = new EntityRecord(ClosingRequest::class, 'OngoingEvaluationTimeThreeAOneA');
        $this->closingRequestOngoingEvaluationTimeThreeAOneA->columns['CustomerAssignment_id'] = $this->customerAssignmentThreeA->columns['id'];
        $this->closingRequestOngoingEvaluationTimeThreeAOneA->columns['createdTime'] = (new DateTime())->format('Y-m-d H:i:s');
        $this->closingRequestOngoingEvaluationTimeThreeAOneA->columns['transactionValue'] = 301;
        $this->closingRequestOngoingEvaluationTimeThreeAOneA->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMinusOneEvaluationTimeOneAOneA = new EntityRecord(ClosingRequest::class, 'MinusOneEvaluationTimeOneAOneA');
        $this->closingRequestMinusOneEvaluationTimeOneAOneA->columns['CustomerAssignment_id'] = $this->customerAssignmentOneA->columns['id'];
        $this->closingRequestMinusOneEvaluationTimeOneAOneA->columns['createdTime'] = (new DateTime('first day of -1 month'))->format('Y-m-d H:i:s');
        $this->closingRequestMinusOneEvaluationTimeOneAOneA->columns['transactionValue'] = 111;
        $this->closingRequestMinusOneEvaluationTimeOneAOneA->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMinusOneEvaluationTimeOneAOneB = new EntityRecord(ClosingRequest::class, 'MinusOneEvaluationTimeOneAOneB');
        $this->closingRequestMinusOneEvaluationTimeOneAOneB->columns['CustomerAssignment_id'] = $this->customerAssignmentOneA->columns['id'];
        $this->closingRequestMinusOneEvaluationTimeOneAOneB->columns['createdTime'] = (new DateTime('first day of -1 month'))->format('Y-m-d H:i:s');
        $this->closingRequestMinusOneEvaluationTimeOneAOneB->columns['transactionValue'] = 112;
        $this->closingRequestMinusOneEvaluationTimeOneAOneB->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMinusOneEvaluationTimeTwoAOneA = new EntityRecord(ClosingRequest::class, 'MinusOneEvaluationTimeTwoAOneA');
        $this->closingRequestMinusOneEvaluationTimeTwoAOneA->columns['CustomerAssignment_id'] = $this->customerAssignmentTwoA->columns['id'];
        $this->closingRequestMinusOneEvaluationTimeTwoAOneA->columns['createdTime'] = (new DateTime('first day of -1 month'))->format('Y-m-d H:i:s');
        $this->closingRequestMinusOneEvaluationTimeTwoAOneA->columns['transactionValue'] = 211;
        $this->closingRequestMinusOneEvaluationTimeTwoAOneA->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMinusOneEvaluationTimeThreeAOneA = new EntityRecord(ClosingRequest::class, 'MinusOneEvaluationTimeThreeAOneA');
        $this->closingRequestMinusOneEvaluationTimeThreeAOneA->columns['CustomerAssignment_id'] = $this->customerAssignmentThreeA->columns['id'];
        $this->closingRequestMinusOneEvaluationTimeThreeAOneA->columns['createdTime'] = (new DateTime('first day of -1 month'))->format('Y-m-d H:i:s');
        $this->closingRequestMinusOneEvaluationTimeThreeAOneA->columns['transactionValue'] = 311;
        $this->closingRequestMinusOneEvaluationTimeThreeAOneA->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMinusTwoEvaluationTimeOneAOneA = new EntityRecord(ClosingRequest::class, 'MinusTwoEvaluationTimeOneAOneA');
        $this->closingRequestMinusTwoEvaluationTimeOneAOneA->columns['CustomerAssignment_id'] = $this->customerAssignmentOneA->columns['id'];
        $this->closingRequestMinusTwoEvaluationTimeOneAOneA->columns['createdTime'] = (new DateTime('first day of -2 month'))->format('Y-m-d H:i:s');
        $this->closingRequestMinusTwoEvaluationTimeOneAOneA->columns['transactionValue'] = 121;
        $this->closingRequestMinusTwoEvaluationTimeOneAOneA->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMinusTwoEvaluationTimeOneAOneB = new EntityRecord(ClosingRequest::class, 'MinusTwoEvaluationTimeOneAOneB');
        $this->closingRequestMinusTwoEvaluationTimeOneAOneB->columns['CustomerAssignment_id'] = $this->customerAssignmentOneA->columns['id'];
        $this->closingRequestMinusTwoEvaluationTimeOneAOneB->columns['createdTime'] = (new DateTime('first day of -2 month'))->format('Y-m-d H:i:s');
        $this->closingRequestMinusTwoEvaluationTimeOneAOneB->columns['transactionValue'] = 122;
        $this->closingRequestMinusTwoEvaluationTimeOneAOneB->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMinusTwoEvaluationTimeTwoAOneA = new EntityRecord(ClosingRequest::class, 'MinusTwoEvaluationTimeTwoAOneA');
        $this->closingRequestMinusTwoEvaluationTimeTwoAOneA->columns['CustomerAssignment_id'] = $this->customerAssignmentTwoA->columns['id'];
        $this->closingRequestMinusTwoEvaluationTimeTwoAOneA->columns['createdTime'] = (new DateTime('first day of -2 month'))->format('Y-m-d H:i:s');
        $this->closingRequestMinusTwoEvaluationTimeTwoAOneA->columns['transactionValue'] = 221;
        $this->closingRequestMinusTwoEvaluationTimeTwoAOneA->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMinusTwoEvaluationTimeThreeAOneA = new EntityRecord(ClosingRequest::class, 'MinusTwoEvaluationTimeThreeAOneA');
        $this->closingRequestMinusTwoEvaluationTimeThreeAOneA->columns['CustomerAssignment_id'] = $this->customerAssignmentThreeA->columns['id'];
        $this->closingRequestMinusTwoEvaluationTimeThreeAOneA->columns['createdTime'] = (new DateTime('first day of -2 month'))->format('Y-m-d H:i:s');
        $this->closingRequestMinusTwoEvaluationTimeThreeAOneA->columns['transactionValue'] = 321;
        $this->closingRequestMinusTwoEvaluationTimeThreeAOneA->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMinusThreeEvaluationTimeOneAOneA = new EntityRecord(ClosingRequest::class, 'MinusThreeEvaluationTimeOneAOneA');
        $this->closingRequestMinusThreeEvaluationTimeOneAOneA->columns['CustomerAssignment_id'] = $this->customerAssignmentOneA->columns['id'];
        $this->closingRequestMinusThreeEvaluationTimeOneAOneA->columns['createdTime'] = (new DateTime('first day of -3 month'))->format('Y-m-d H:i:s');
        $this->closingRequestMinusThreeEvaluationTimeOneAOneA->columns['transactionValue'] = 131;
        $this->closingRequestMinusThreeEvaluationTimeOneAOneA->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMinusThreeEvaluationTimeOneAOneB = new EntityRecord(ClosingRequest::class, 'MinusThreeEvaluationTimeOneAOneB');
        $this->closingRequestMinusThreeEvaluationTimeOneAOneB->columns['CustomerAssignment_id'] = $this->customerAssignmentOneA->columns['id'];
        $this->closingRequestMinusThreeEvaluationTimeOneAOneB->columns['createdTime'] = (new DateTime('first day of -3 month'))->format('Y-m-d H:i:s');
        $this->closingRequestMinusThreeEvaluationTimeOneAOneB->columns['transactionValue'] = 132;
        $this->closingRequestMinusThreeEvaluationTimeOneAOneB->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMinusThreeEvaluationTimeTwoAOneA = new EntityRecord(ClosingRequest::class, 'MinusThreeEvaluationTimeTwoAOneA');
        $this->closingRequestMinusThreeEvaluationTimeTwoAOneA->columns['CustomerAssignment_id'] = $this->customerAssignmentTwoA->columns['id'];
        $this->closingRequestMinusThreeEvaluationTimeTwoAOneA->columns['createdTime'] = (new DateTime('first day of -3 month'))->format('Y-m-d H:i:s');
        $this->closingRequestMinusThreeEvaluationTimeTwoAOneA->columns['transactionValue'] = 231;
        $this->closingRequestMinusThreeEvaluationTimeTwoAOneA->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMinusThreeEvaluationTimeThreeAOneA = new EntityRecord(ClosingRequest::class, 'MinusThreeEvaluationTimeThreeAOneA');
        $this->closingRequestMinusThreeEvaluationTimeThreeAOneA->columns['CustomerAssignment_id'] = $this->customerAssignmentThreeA->columns['id'];
        $this->closingRequestMinusThreeEvaluationTimeThreeAOneA->columns['createdTime'] = (new DateTime('first day of -3 month'))->format('Y-m-d H:i:s');
        $this->closingRequestMinusThreeEvaluationTimeThreeAOneA->columns['transactionValue'] = 331;
        $this->closingRequestMinusThreeEvaluationTimeThreeAOneA->columns['status'] = ManagementApprovalStatus::APPROVED->value;
    }
    
    protected function tearDown(): void
    {
//        parent::tearDown();
//        $this->connection->table('CompanyMetric')->truncate();
//        $this->connection->table('SalesRank')->truncate();
//        $this->connection->table('SalesPerformanceMetric')->truncate();
//        $this->connection->table('SalesPerformanceMetricEvaluation')->truncate();
//        $this->connection->table('Sales')->truncate();
//        $this->connection->table('CustomerAssignment')->truncate();
//        $this->connection->table('SalesActivitySchedule')->truncate();
//        $this->connection->table('SalesActivityReport')->truncate();
//        $this->connection->table('ClosingRequest')->truncate();
    }
    
    protected function viewAllCompanyMetricSummary()
    {
        $this->prepareAdminDependency();
        //
        $this->companyMetricOne->insert($this->connection);
        $this->companyMetricTwo->insert($this->connection);
        
        $this->salesOne->insert($this->connection);
        $this->salesTwo->insert($this->connection);
        $this->salesThree->insert($this->connection);
        
        $this->customerAssignmentOneA->insert($this->connection);
        $this->customerAssignmentTwoA->insert($this->connection);
        $this->customerAssignmentThreeA->insert($this->connection);
        
        $this->salesActivityScheduleOneAOne->insert($this->connection);
        $this->salesActivityScheduleTwoAOne->insert($this->connection);
        $this->salesActivityScheduleThreeAOne->insert($this->connection);
        
        $this->salesActivityReportOngoingEvaluationTimeOneAOneA->insert($this->connection);
        $this->salesActivityReportOngoingEvaluationTimeOneAOneB->insert($this->connection);
        $this->salesActivityReportOngoingEvaluationTimeOneAOneC->insert($this->connection);
        $this->salesActivityReportOngoingEvaluationTimeTwoAOneA->insert($this->connection);
        $this->salesActivityReportOngoingEvaluationTimeThreeAOneA->insert($this->connection);
        $this->salesActivityReportMinusOneEvaluationTimeOneAOneA->insert($this->connection);
        $this->salesActivityReportMinusOneEvaluationTimeOneAOneB->insert($this->connection);
        $this->salesActivityReportMinusOneEvaluationTimeTwoAOneA->insert($this->connection);
        $this->salesActivityReportMinusOneEvaluationTimeThreeAOneA->insert($this->connection);
        $this->salesActivityReportMinusTwoEvaluationTimeOneAOneA->insert($this->connection);
        $this->salesActivityReportMinusTwoEvaluationTimeOneAOneB->insert($this->connection);
        $this->salesActivityReportMinusTwoEvaluationTimeTwoAOneA->insert($this->connection);
        $this->salesActivityReportMinusTwoEvaluationTimeThreeAOneA->insert($this->connection);
        $this->salesActivityReportMinusThreeEvaluationTimeOneAOneA->insert($this->connection);
        $this->salesActivityReportMinusThreeEvaluationTimeOneAOneB->insert($this->connection);
        $this->salesActivityReportMinusThreeEvaluationTimeTwoAOneA->insert($this->connection);
        $this->salesActivityReportMinusThreeEvaluationTimeThreeAOneA->insert($this->connection);
        
        $this->closingRequestOngoingEvaluationTimeOneAOneA->insert($this->connection);
        $this->closingRequestOngoingEvaluationTimeOneAOneB->insert($this->connection);
        $this->closingRequestOngoingEvaluationTimeOneAOneC->insert($this->connection);
        $this->closingRequestOngoingEvaluationTimeTwoAOneA->insert($this->connection);
        $this->closingRequestOngoingEvaluationTimeThreeAOneA->insert($this->connection);
        $this->closingRequestMinusOneEvaluationTimeOneAOneA->insert($this->connection);
        $this->closingRequestMinusOneEvaluationTimeOneAOneB->insert($this->connection);
        $this->closingRequestMinusOneEvaluationTimeTwoAOneA->insert($this->connection);
        $this->closingRequestMinusOneEvaluationTimeThreeAOneA->insert($this->connection);
        $this->closingRequestMinusTwoEvaluationTimeOneAOneA->insert($this->connection);
        $this->closingRequestMinusTwoEvaluationTimeOneAOneB->insert($this->connection);
        $this->closingRequestMinusTwoEvaluationTimeTwoAOneA->insert($this->connection);
        $this->closingRequestMinusTwoEvaluationTimeThreeAOneA->insert($this->connection);
        $this->closingRequestMinusThreeEvaluationTimeOneAOneA->insert($this->connection);
        $this->closingRequestMinusThreeEvaluationTimeOneAOneB->insert($this->connection);
        $this->closingRequestMinusThreeEvaluationTimeTwoAOneA->insert($this->connection);
        $this->closingRequestMinusThreeEvaluationTimeThreeAOneA->insert($this->connection);
        
        
        $this->response = $this->get('api/view-all-company-metric-summary', $this->admin->token);
    }
    public function test_viewAllCompanyMetricSummary_200()
    {
$this->disableExceptionHandling();
        $this->viewAllCompanyMetricSummary();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'name' => $this->companyMetricOne->columns['name'],
            'target' => $this->companyMetricOne->columns['target'],
            'result' => [
                [
                    'evaluationTime' => (new DateTime())->format('Y-m'),
                    'achievement' => strval(101+102+103+201+301),
                ],
                [
                    'evaluationTime' => (new DateTime('first day of -1 month'))->format('Y-m'),
                    'achievement' => strval(111+112+211+311),
                ],
                [
                    'evaluationTime' => (new DateTime('first day of -2 month'))->format('Y-m'),
                    'achievement' => strval(121+122+221+321),
                ],
            ],
        ]);
        $this->seeJsonContains([
            'name' => $this->companyMetricTwo->columns['name'],
            'target' => $this->companyMetricTwo->columns['target'],
            'result' => [
                [
                    'evaluationTime' => (new DateTime())->format('Y-m'),
                    'achievement' => 5,
                ],
                [
                    'evaluationTime' => (new DateTime('first day of -1 month'))->format('Y-m'),
                    'achievement' => 4,
                ],
                [
                    'evaluationTime' => (new DateTime('first day of -2 month'))->format('Y-m'),
                    'achievement' => 4,
                ],
            ],
        ]);
    }
    
    protected function viewAllSalesRankSummary()
    {
        $this->prepareAdminDependency();
        //
        $this->salesRankOne->insert($this->connection);
        $this->salesRankTwo->insert($this->connection);
        
        $this->salesOne->insert($this->connection);
        $this->salesTwo->insert($this->connection);
        $this->salesThree->insert($this->connection);
        
        $this->customerAssignmentOneA->insert($this->connection);
        $this->customerAssignmentTwoA->insert($this->connection);
        $this->customerAssignmentThreeA->insert($this->connection);
        
        $this->salesActivityScheduleOneAOne->insert($this->connection);
        $this->salesActivityScheduleTwoAOne->insert($this->connection);
        $this->salesActivityScheduleThreeAOne->insert($this->connection);
        
        $this->salesActivityReportOngoingEvaluationTimeOneAOneA->insert($this->connection);
        $this->salesActivityReportOngoingEvaluationTimeOneAOneB->insert($this->connection);
        $this->salesActivityReportOngoingEvaluationTimeOneAOneC->insert($this->connection);
        $this->salesActivityReportOngoingEvaluationTimeTwoAOneA->insert($this->connection);
        $this->salesActivityReportOngoingEvaluationTimeThreeAOneA->insert($this->connection);
        $this->salesActivityReportMinusOneEvaluationTimeOneAOneA->insert($this->connection);
        $this->salesActivityReportMinusOneEvaluationTimeOneAOneB->insert($this->connection);
        $this->salesActivityReportMinusOneEvaluationTimeTwoAOneA->insert($this->connection);
        $this->salesActivityReportMinusOneEvaluationTimeThreeAOneA->insert($this->connection);
        $this->salesActivityReportMinusTwoEvaluationTimeOneAOneA->insert($this->connection);
        $this->salesActivityReportMinusTwoEvaluationTimeOneAOneB->insert($this->connection);
        $this->salesActivityReportMinusTwoEvaluationTimeTwoAOneA->insert($this->connection);
        $this->salesActivityReportMinusTwoEvaluationTimeThreeAOneA->insert($this->connection);
        $this->salesActivityReportMinusThreeEvaluationTimeOneAOneA->insert($this->connection);
        $this->salesActivityReportMinusThreeEvaluationTimeOneAOneB->insert($this->connection);
        $this->salesActivityReportMinusThreeEvaluationTimeTwoAOneA->insert($this->connection);
        $this->salesActivityReportMinusThreeEvaluationTimeThreeAOneA->insert($this->connection);
        
        $this->closingRequestOngoingEvaluationTimeOneAOneA->insert($this->connection);
        $this->closingRequestOngoingEvaluationTimeOneAOneB->insert($this->connection);
        $this->closingRequestOngoingEvaluationTimeOneAOneC->insert($this->connection);
        $this->closingRequestOngoingEvaluationTimeTwoAOneA->insert($this->connection);
        $this->closingRequestOngoingEvaluationTimeThreeAOneA->insert($this->connection);
        $this->closingRequestMinusOneEvaluationTimeOneAOneA->insert($this->connection);
        $this->closingRequestMinusOneEvaluationTimeOneAOneB->insert($this->connection);
        $this->closingRequestMinusOneEvaluationTimeTwoAOneA->insert($this->connection);
        $this->closingRequestMinusOneEvaluationTimeThreeAOneA->insert($this->connection);
        $this->closingRequestMinusTwoEvaluationTimeOneAOneA->insert($this->connection);
        $this->closingRequestMinusTwoEvaluationTimeOneAOneB->insert($this->connection);
        $this->closingRequestMinusTwoEvaluationTimeTwoAOneA->insert($this->connection);
        $this->closingRequestMinusTwoEvaluationTimeThreeAOneA->insert($this->connection);
        $this->closingRequestMinusThreeEvaluationTimeOneAOneA->insert($this->connection);
        $this->closingRequestMinusThreeEvaluationTimeOneAOneB->insert($this->connection);
        $this->closingRequestMinusThreeEvaluationTimeTwoAOneA->insert($this->connection);
        $this->closingRequestMinusThreeEvaluationTimeThreeAOneA->insert($this->connection);
        
        
        $this->response = $this->get('api/view-all-sales-rank-summary', $this->admin->token);
    }
    public function test_viewAllSalesRankSummary_200()
    {
$this->disableExceptionHandling();
        $this->viewAllSalesRankSummary();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'name' => $this->salesRankOne->columns['name'],
            'result' => [
                [
                    'id' => $this->salesOne->columns['id'],
                    'name' => $this->salesOne->columns['name'],
                    'evaluationTime' => (new DateTime())->format('Y-m'),
                    'achievement' => strval(101+102+103),
                ],
                [
                    'id' => $this->salesThree->columns['id'],
                    'name' => $this->salesThree->columns['name'],
                    'evaluationTime' => (new DateTime())->format('Y-m'),
                    'achievement' => strval(301),
                ],
            ],
        ]);
        $this->seeJsonContains([
            'name' => $this->salesRankTwo->columns['name'],
            'result' => [
                [
                    'id' => $this->salesThree->columns['id'],
                    'name' => $this->salesThree->columns['name'],
                    'evaluationTime' => (new DateTime())->format('Y-m'),
                    'achievement' => 1,
                ],
                [
                    'id' => $this->salesTwo->columns['id'],
                    'name' => $this->salesTwo->columns['name'],
                    'evaluationTime' => (new DateTime())->format('Y-m'),
                    'achievement' => 1,
                ],
            ],
        ]);
    }
    
    protected function viewAllSalesPerformanceMetricSummary()
    {
        $this->prepareAdminDependency();
        //
        $this->salesPerformanceMetricOne->insert($this->connection);
        $this->salesPerformanceMetricTwo->insert($this->connection);
        $this->salesPerformanceMetricEvaluationOneA->insert($this->connection);
        $this->salesPerformanceMetricEvaluationOneB->insert($this->connection);
        $this->salesPerformanceMetricEvaluationOneC->insert($this->connection);
        $this->salesPerformanceMetricEvaluationTwoA->insert($this->connection);
        
        $this->salesOne->insert($this->connection);
        $this->salesTwo->insert($this->connection);
        $this->salesThree->insert($this->connection);
        
        $this->customerAssignmentOneA->insert($this->connection);
        $this->customerAssignmentTwoA->insert($this->connection);
        $this->customerAssignmentThreeA->insert($this->connection);
        
        $this->salesActivityScheduleOneAOne->insert($this->connection);
        $this->salesActivityScheduleTwoAOne->insert($this->connection);
        $this->salesActivityScheduleThreeAOne->insert($this->connection);
        
        $this->salesActivityReportOngoingEvaluationTimeOneAOneA->insert($this->connection);
        $this->salesActivityReportOngoingEvaluationTimeOneAOneB->insert($this->connection);
        $this->salesActivityReportOngoingEvaluationTimeOneAOneC->insert($this->connection);
        $this->salesActivityReportOngoingEvaluationTimeTwoAOneA->insert($this->connection);
        $this->salesActivityReportOngoingEvaluationTimeThreeAOneA->insert($this->connection);
        $this->salesActivityReportMinusOneEvaluationTimeOneAOneA->insert($this->connection);
        $this->salesActivityReportMinusOneEvaluationTimeOneAOneB->insert($this->connection);
        $this->salesActivityReportMinusOneEvaluationTimeTwoAOneA->insert($this->connection);
        $this->salesActivityReportMinusOneEvaluationTimeThreeAOneA->insert($this->connection);
        $this->salesActivityReportMinusTwoEvaluationTimeOneAOneA->insert($this->connection);
        $this->salesActivityReportMinusTwoEvaluationTimeOneAOneB->insert($this->connection);
        $this->salesActivityReportMinusTwoEvaluationTimeTwoAOneA->insert($this->connection);
        $this->salesActivityReportMinusTwoEvaluationTimeThreeAOneA->insert($this->connection);
        $this->salesActivityReportMinusThreeEvaluationTimeOneAOneA->insert($this->connection);
        $this->salesActivityReportMinusThreeEvaluationTimeOneAOneB->insert($this->connection);
        $this->salesActivityReportMinusThreeEvaluationTimeTwoAOneA->insert($this->connection);
        $this->salesActivityReportMinusThreeEvaluationTimeThreeAOneA->insert($this->connection);
        
        $this->closingRequestOngoingEvaluationTimeOneAOneA->insert($this->connection);
        $this->closingRequestOngoingEvaluationTimeOneAOneB->insert($this->connection);
        $this->closingRequestOngoingEvaluationTimeOneAOneC->insert($this->connection);
        $this->closingRequestOngoingEvaluationTimeTwoAOneA->insert($this->connection);
        $this->closingRequestOngoingEvaluationTimeThreeAOneA->insert($this->connection);
        $this->closingRequestMinusOneEvaluationTimeOneAOneA->insert($this->connection);
        $this->closingRequestMinusOneEvaluationTimeOneAOneB->insert($this->connection);
        $this->closingRequestMinusOneEvaluationTimeTwoAOneA->insert($this->connection);
        $this->closingRequestMinusOneEvaluationTimeThreeAOneA->insert($this->connection);
        $this->closingRequestMinusTwoEvaluationTimeOneAOneA->insert($this->connection);
        $this->closingRequestMinusTwoEvaluationTimeOneAOneB->insert($this->connection);
        $this->closingRequestMinusTwoEvaluationTimeTwoAOneA->insert($this->connection);
        $this->closingRequestMinusTwoEvaluationTimeThreeAOneA->insert($this->connection);
        $this->closingRequestMinusThreeEvaluationTimeOneAOneA->insert($this->connection);
        $this->closingRequestMinusThreeEvaluationTimeOneAOneB->insert($this->connection);
        $this->closingRequestMinusThreeEvaluationTimeTwoAOneA->insert($this->connection);
        $this->closingRequestMinusThreeEvaluationTimeThreeAOneA->insert($this->connection);
        
        
        $this->response = $this->get('api/view-all-sales-performance-metric-summary', $this->admin->token);
    }
    public function test_viewAllSalesPerformanceMetricSummary_200()
    {
$this->disableExceptionHandling();
        $this->viewAllSalesPerformanceMetricSummary();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'name' => $this->salesPerformanceMetricOne->columns['name'],
            'result' => [
                [
                    'evaluationTime' => (new DateTime())->format('Y-m'),
                    'maxTransaction' => strval(101+102+103),
                    'minTransaction' => strval(201),
                    'avgTransaction' => number_format((101+102+103+201+301)/3, 4, '.', ''),
                ],
                [
                    'evaluationTime' => (new DateTime('first day of -1 month'))->format('Y-m'),
                    'maxTransaction' => strval(311),
                    'minTransaction' => strval(211),
                    'avgTransaction' => number_format((111+112+211+311)/3, 4, '.', ''),
                ],
                [
                    'evaluationTime' => (new DateTime('first day of -2 month'))->format('Y-m'),
                    'maxTransaction' => strval(321),
                    'minTransaction' => strval(221),
                    'avgTransaction' => number_format((121+122+221+321)/3, 4, '.', ''),
                ],
            ],
        ]);
        $this->seeJsonContains([
            'name' => $this->salesPerformanceMetricTwo->columns['name'],
            'result' => [
                [
                    'evaluationTime' => (new DateTime())->format('Y-m'),
                    'avgActivity' => number_format(5/3, 4, '.', ''),
                ],
                [
                    'evaluationTime' => (new DateTime('first day of -1 month'))->format('Y-m'),
                    'avgActivity' => number_format(4/3, 4, '.', ''),
                ],
                [
                    'evaluationTime' => (new DateTime('first day of -2 month'))->format('Y-m'),
                    'avgActivity' => number_format(4/3, 4, '.', ''),
                ],
            ],
        ]);
    }
}
