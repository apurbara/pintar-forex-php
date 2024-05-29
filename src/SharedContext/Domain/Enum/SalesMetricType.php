<?php

namespace SharedContext\Domain\Enum;

use Doctrine\DBAL\Query\QueryBuilder;

enum SalesMetricType: string
{

    case SALES_ACTIVITY_REPORT = 'SALES_ACTIVITY_REPORT';
    case APPROVED_CLOSING_REQUEST = 'APPROVED_CLOSING_REQUEST';

    public function applyToQueryBuilder(QueryBuilder $qb, string $salesId): void
    {
        match ($this) {
            SalesMetricType::SALES_ACTIVITY_REPORT => $this->applySalesActivityReportMetric($qb, $salesId),
            SalesMetricType::APPROVED_CLOSING_REQUEST => $this->applyApprovedClosingRequestMetric($qb, $salesId)
        };
    }
    
    public function getMetricEvaluationColumn(): string
    {
        return match ($this){
            SalesMetricType::SALES_ACTIVITY_REPORT => 'SalesActivityReport.id',
            SalesMetricType::APPROVED_CLOSING_REQUEST => 'ClosingRequest.transactionValue'
        };
    }
    
    public function getMetricTimeColumn(): string
    {
        return match ($this){
            SalesMetricType::SALES_ACTIVITY_REPORT => 'SalesActivityReport.submitTime',
            SalesMetricType::APPROVED_CLOSING_REQUEST => 'ClosingRequest.createdTime'
        };
    }

    protected function applySalesActivityReportMetric(QueryBuilder $qb, string $salesId): void
    {
        $qb->from('SalesActivityReport')
                ->innerJoin('SalesActivityReport', 'SalesActivitySchedule', 'SalesActivitySchedule',
                        'SalesActivityReport.SalesActivitySchedule_id = SalesActivitySchedule.id')
                ->innerJoin('SalesActivitySchedule', 'CustomerAssignment', 'CustomerAssignment',
                        'SalesActivitySchedule.CustomerAssignment_id = CustomerAssignment.id')
                ->andWhere($qb->expr()->eq('CustomerAssignment.Sales_id', ':salesId'))
                ->setParameter('salesId', $salesId);
    }

    protected function applyApprovedClosingRequestMetric(QueryBuilder $qb, string $salesId): void
    {
        $approvedClosingRequestStatus = ManagementApprovalStatus::APPROVED->value;
        $qb->from('ClosingRequest')
                ->innerJoin('ClosingRequest', 'CustomerAssignment', 'CustomerAssignment',
                        "ClosingRequest.CustomerAssignment_id = CustomerAssignment.id AND ClosingRequest.status = '$approvedClosingRequestStatus'")
                ->andWhere($qb->expr()->eq('CustomerAssignment.Sales_id', ':salesId'))
                ->setParameter('salesId', $salesId);
    }
}
