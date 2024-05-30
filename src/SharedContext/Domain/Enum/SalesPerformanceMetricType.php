<?php

namespace SharedContext\Domain\Enum;

use Doctrine\DBAL\Query\QueryBuilder;

enum SalesPerformanceMetricType: string
{

    case SALES_ACTIVITY_REPORT = 'SALES_ACTIVITY_REPORT';
    case APPROVED_CLOSING_REQUEST_SUM = 'APPROVED_CLOSING_REQUEST_SUM';
    case APPROVED_CLOSING_REQUEST_COUNT = 'APPROVED_CLOSING_REQUEST_COUNT';

    public function applyToQuery(QueryBuilder $qb, RecurrenceType $recurrenceType, ?int $recurrenceCount): void
    {
        match ($this) {
            SalesPerformanceMetricType::SALES_ACTIVITY_REPORT => $this->applySalesActivityReportMetric($qb,
                    $recurrenceType, $recurrenceCount),
            SalesPerformanceMetricType::APPROVED_CLOSING_REQUEST_SUM => $this->applyApprovedClosingRequestSumMetric($qb,
                    $recurrenceType, $recurrenceCount),
            SalesPerformanceMetricType::APPROVED_CLOSING_REQUEST_COUNT => $this->applyApprovedClosingRequestCountMetric($qb,
                    $recurrenceType, $recurrenceCount),
        };
    }

    protected function applySalesActivityReportMetric(QueryBuilder $qb, RecurrenceType $recurrenceType,
            ?int $recurrenceCount): void
    {
        $qb->select("COUNT(SalesActivityReport.id) achievement")
                ->addSelect('Sales.id')
                ->from('Sales')
                ->leftJoin('Sales', 'CustomerAssignment', 'CustomerAssignment', 'CustomerAssignment.Sales_id = Sales.id')
                ->leftJoin('CustomerAssignment', 'SalesActivitySchedule', 'SalesActivitySchedule',
                        'SalesActivitySchedule.CustomerAssignment_id = CustomerAssignment.id')
                ->leftJoin('SalesActivitySchedule', 'SalesActivityReport', 'SalesActivityReport',
                        'SalesActivityReport.SalesActivitySchedule_id = SalesActivitySchedule.id')
                ->addGroupBy('Sales.id');
        $recurrenceType->applyToQuery($qb, 'SalesActivityReport.submitTime', $recurrenceCount);
    }

    protected function applyApprovedClosingRequestSumMetric(QueryBuilder $qb, RecurrenceType $recurrenceType,
            ?int $recurrenceCount): void
    {
        $approvedClosingRequestStatus = ManagementApprovalStatus::APPROVED->value;
        $qb->select("SUM(ClosingRequest.transactionValue) achievement")
                ->addSelect('Sales.id')
                ->from('Sales')
                ->leftJoin('Sales', 'CustomerAssignment', 'CustomerAssignment', 'CustomerAssignment.Sales_id = Sales.id')
                ->leftJoin('CustomerAssignment', 'ClosingRequest', 'ClosingRequest',
                        "ClosingRequest.CustomerAssignment_id = CustomerAssignment.id AND ClosingRequest.status = '{$approvedClosingRequestStatus}'")
                ->addGroupBy('Sales.id');
        $recurrenceType->applyToQuery($qb, 'SalesActivityReport.submitTime', $recurrenceCount);
    }

    protected function applyApprovedClosingRequestCountMetric(QueryBuilder $qb, RecurrenceType $recurrenceType,
            ?int $recurrenceCount): void
    {
        $approvedClosingRequestStatus = ManagementApprovalStatus::APPROVED->value;
        $qb->select("COUNT(ClosingRequest.transactionValue) achievement")
                ->addSelect('Sales.id')
                ->from('Sales')
                ->leftJoin('Sales', 'CustomerAssignment', 'CustomerAssignment', 'CustomerAssignment.Sales_id = Sales.id')
                ->leftJoin('CustomerAssignment', 'ClosingRequest', 'ClosingRequest',
                        "ClosingRequest.CustomerAssignment_id = CustomerAssignment.id AND ClosingRequest.status = '{$approvedClosingRequestStatus}'")
                ->addGroupBy('Sales.id');
        $recurrenceType->applyToQuery($qb, 'SalesActivityReport.submitTime', $recurrenceCount);
    }
}
