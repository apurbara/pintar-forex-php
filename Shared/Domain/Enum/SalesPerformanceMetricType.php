<?php

namespace Shared\Domain\Enum;

use Doctrine\DBAL\Query\QueryBuilder;

enum SalesPerformanceMetricType: string
{

    case GREETING_ACTIVITY_REPORT_COUNT = 'GREETING_ACTIVITY_REPORT_COUNT';
    case FACT_FINDING_ACTIVITY_REPORT_COUNT = 'FACT_FINDING_ACTIVITY_REPORT_COUNT';
    case STRIKING_ACTIVITY_REPORT_COUNT = 'STRIKING_ACTIVITY_REPORT_COUNT';
    case SUCCESSFULL_GREETING_COUNT = 'SUCCESSFULL_GREETING_COUNT';
    case SUCCESSFULL_FACT_FINDING_COUNT = 'SUCCESSFULL_FACT_FINDING_COUNT';
    case APPROVED_CLOSING_REQUEST_SUM = 'APPROVED_CLOSING_REQUEST_SUM';
    case APPROVED_CLOSING_REQUEST_COUNT = 'APPROVED_CLOSING_REQUEST_COUNT';

    public function applyToQuery(QueryBuilder $qb, RecurrenceType $recurrenceType, ?int $recurrenceCount): void
    {
        match ($this) {
            SalesPerformanceMetricType::GREETING_ACTIVITY_REPORT_COUNT => $this->applyGreetingActivityReportMetric($qb, $recurrenceType, $recurrenceCount),
            SalesPerformanceMetricType::FACT_FINDING_ACTIVITY_REPORT_COUNT => $this->applyFactFindingActivityReportMetric($qb, $recurrenceType, $recurrenceCount),
            SalesPerformanceMetricType::STRIKING_ACTIVITY_REPORT_COUNT => $this->applyStrikingActivityReportMetric($qb, $recurrenceType, $recurrenceCount),
            SalesPerformanceMetricType::SUCCESSFULL_GREETING_COUNT => $this->applySuccessfullGreetingCountMetric($qb, $recurrenceType, $recurrenceCount),
            SalesPerformanceMetricType::SUCCESSFULL_FACT_FINDING_COUNT => $this->applySuccessfullFactFindingCountMetric($qb, $recurrenceType, $recurrenceCount),
            SalesPerformanceMetricType::APPROVED_CLOSING_REQUEST_SUM => $this->applyApprovedClosingRequestSumMetric($qb, $recurrenceType, $recurrenceCount),
            SalesPerformanceMetricType::APPROVED_CLOSING_REQUEST_COUNT => $this->applyApprovedClosingRequestCountMetric($qb, $recurrenceType, $recurrenceCount),
        };
    }

    protected function applyGreetingActivityReportMetric(QueryBuilder $qb, RecurrenceType $recurrenceType,
            ?int $recurrenceCount): void
    {
        $qb->select("COUNT(SalesActivityReport.id) achievement")
                ->addSelect('Sales.id')
                ->from('Sales')
                ->leftJoin('Sales', 'GreetingAssignment', 'GreetingAssignment', 'GreetingAssignment.Sales_id = Sales.id')
                ->leftJoin('GreetingAssignment', 'CustomerAssignment', 'CustomerAssignment', 'GreetingAssignment.CustomerAssignment_id = CustomerAssignment.id')
                ->leftJoin('CustomerAssignment', 'SalesActivitySchedule', 'SalesActivitySchedule',
                        'SalesActivitySchedule.CustomerAssignment_id = CustomerAssignment.id')
                ->leftJoin('SalesActivitySchedule', 'SalesActivityReport', 'SalesActivityReport',
                        'SalesActivityReport.SalesActivitySchedule_id = SalesActivitySchedule.id')
                ->addGroupBy('Sales.id');
        $recurrenceType->applyToQuery($qb, 'SalesActivityReport.submitTime', $recurrenceCount);
    }

    protected function applyFactFindingActivityReportMetric(QueryBuilder $qb, RecurrenceType $recurrenceType,
            ?int $recurrenceCount): void
    {
        $qb->select("COUNT(SalesActivityReport.id) achievement")
                ->addSelect('Sales.id')
                ->from('Sales')
                ->leftJoin('Sales', 'FactFindingAssignment', 'FactFindingAssignment', 'FactFindingAssignment.Sales_id = Sales.id')
                ->leftJoin('FactFindingAssignment', 'CustomerAssignment', 'CustomerAssignment', 'FactFindingAssignment.CustomerAssignment_id = CustomerAssignment.id')
                ->leftJoin('CustomerAssignment', 'SalesActivitySchedule', 'SalesActivitySchedule',
                        'SalesActivitySchedule.CustomerAssignment_id = CustomerAssignment.id')
                ->leftJoin('SalesActivitySchedule', 'SalesActivityReport', 'SalesActivityReport',
                        'SalesActivityReport.SalesActivitySchedule_id = SalesActivitySchedule.id')
                ->addGroupBy('Sales.id');
        $recurrenceType->applyToQuery($qb, 'SalesActivityReport.submitTime', $recurrenceCount);
    }

    protected function applyStrikingActivityReportMetric(QueryBuilder $qb, RecurrenceType $recurrenceType,
            ?int $recurrenceCount): void
    {
        $qb->select("COUNT(SalesActivityReport.id) achievement")
                ->addSelect('Sales.id')
                ->from('Sales')
                ->leftJoin('Sales', 'StrikingAssignment', 'StrikingAssignment', 'StrikingAssignment.Sales_id = Sales.id')
                ->leftJoin('StrikingAssignment', 'CustomerAssignment', 'CustomerAssignment', 'StrikingAssignment.CustomerAssignment_id = CustomerAssignment.id')
                ->leftJoin('CustomerAssignment', 'SalesActivitySchedule', 'SalesActivitySchedule',
                        'SalesActivitySchedule.CustomerAssignment_id = CustomerAssignment.id')
                ->leftJoin('SalesActivitySchedule', 'SalesActivityReport', 'SalesActivityReport',
                        'SalesActivityReport.SalesActivitySchedule_id = SalesActivitySchedule.id')
                ->addGroupBy('Sales.id');
        $recurrenceType->applyToQuery($qb, 'SalesActivityReport.submitTime', $recurrenceCount);
    }

    //
    protected function applySuccessfullGreetingCountMetric(QueryBuilder $qb, RecurrenceType $recurrenceType,
            ?int $recurrenceCount): void
    {
        $successfullGreetingStatus = CustomerAssignmentStatus::COMPLETED->value;
        $qb->select("COUNT(GreetingAssignment.id) achievement")
                ->addSelect('Sales.id')
                ->from('Sales')
                ->leftJoin('Sales', 'GreetingAssignment', 'GreetingAssignment', 'GreetingAssignment.Sales_id = Sales.id')
                ->leftJoin('GreetingAssignment', 'CustomerAssignment', 'CustomerAssignment', 'GreetingAssignment.CustomerAssignment_id = CustomerAssignment.id')
                ->addGroupBy('Sales.id');
        $recurrenceType->applyToQuery($qb, 'CustomerAssignment.completedTime', $recurrenceCount);
    }
    
    protected function applySuccessfullFactFindingCountMetric(QueryBuilder $qb, RecurrenceType $recurrenceType,
            ?int $recurrenceCount): void
    {
        $successfullFactFindingStatus = CustomerAssignmentStatus::COMPLETED->value;
        $qb->select("COUNT(FactFindingAssignment.id) achievement")
                ->addSelect('Sales.id')
                ->from('Sales')
                ->leftJoin('Sales', 'FactFindingAssignment', 'FactFindingAssignment', 'FactFindingAssignment.Sales_id = Sales.id')
                ->leftJoin('FactFindingAssignment', 'CustomerAssignment', 'CustomerAssignment', 'FactFindingAssignment.CustomerAssignment_id = CustomerAssignment.id')
                ->addGroupBy('Sales.id');
        $recurrenceType->applyToQuery($qb, 'CustomerAssignment.completedTime', $recurrenceCount);
    }
    
    protected function applyApprovedClosingRequestSumMetric(QueryBuilder $qb, RecurrenceType $recurrenceType,
            ?int $recurrenceCount): void
    {
        $approvedClosingRequestStatus = ManagementApprovalStatus::APPROVED->value;
        $qb->select("SUM(ClosingRequest.transactionValue) achievement")
                ->addSelect('Sales.id')
                ->from('Sales')
                ->leftJoin('Sales', 'StrikingAssignment', 'StrikingAssignment', 'StrikingAssignment.Sales_id = Sales.id')
                ->leftJoin('StrikingAssignment', 'ClosingRequest', 'ClosingRequest',
                        "ClosingRequest.StrikingAssignment_id = StrikingAssignment.id AND ClosingRequest.status = '{$approvedClosingRequestStatus}'")
                ->addGroupBy('Sales.id');
        $recurrenceType->applyToQuery($qb, 'ClosingRequest.createdTime', $recurrenceCount);
    }

    protected function applyApprovedClosingRequestCountMetric(QueryBuilder $qb, RecurrenceType $recurrenceType,
            ?int $recurrenceCount): void
    {
        $approvedClosingRequestStatus = ManagementApprovalStatus::APPROVED->value;
        $qb->select("COUNT(ClosingRequest.transactionValue) achievement")
                ->addSelect('Sales.id')
                ->from('Sales')
                ->leftJoin('Sales', 'StrikingAssignment', 'StrikingAssignment', 'StrikingAssignment.Sales_id = Sales.id')
                ->leftJoin('StrikingAssignment', 'ClosingRequest', 'ClosingRequest',
                        "ClosingRequest.StrikingAssignment_id = StrikingAssignment.id AND ClosingRequest.status = '{$approvedClosingRequestStatus}'")
                ->addGroupBy('Sales.id');
        $recurrenceType->applyToQuery($qb, 'ClosingRequest.createdTime', $recurrenceCount);
    }
}
