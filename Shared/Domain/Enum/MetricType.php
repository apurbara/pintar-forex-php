<?php

namespace Shared\Domain\Enum;

use Doctrine\DBAL\Query\QueryBuilder;

enum MetricType: string
{

    case GREETING_ACTIVITY_REPORT = 'GREETING_ACTIVITY_REPORT';
    case FACT_FINDING_ACTIVITY_REPORT = 'FACT_FINDING_ACTIVITY_REPORT';
    case STRIKING_ACTIVITY_REPORT = 'STRIKING_ACTIVITY_REPORT';
    case SUCCESSFULL_GREETING = 'SUCCESSFULL_GREETING';
    case SUCCESSFULL_FACT_FINDING = 'SUCCESSFULL_FACT_FINDING';
    case APPROVED_CLOSING_REQUEST = 'APPROVED_CLOSING_REQUEST';

    public function applyToQuery(QueryBuilder $qb, RecurrenceType $recurrenceType, EvaluationType $evaluationType,
            ?int $recurrenceCount)
    {
        match ($this) {
            MetricType::GREETING_ACTIVITY_REPORT => $this->applyGreetingActivityReportMetric($qb, $recurrenceType,
                    $evaluationType, $recurrenceCount),
            MetricType::FACT_FINDING_ACTIVITY_REPORT => $this->applyFactFindingActivityReportMetric($qb,
                    $recurrenceType, $evaluationType, $recurrenceCount),
            MetricType::STRIKING_ACTIVITY_REPORT => $this->applyStrikingActivityReportMetric($qb, $recurrenceType,
                    $evaluationType, $recurrenceCount),
            MetricType::SUCCESSFULL_GREETING => $this->applySuccessfullGreetingMetric($qb, $recurrenceType,
                    $evaluationType, $recurrenceCount),
            MetricType::SUCCESSFULL_FACT_FINDING => $this->applySuccessfullFactFindingMetric($qb, $recurrenceType,
                    $evaluationType, $recurrenceCount),
            MetricType::APPROVED_CLOSING_REQUEST => $this->applyApprovedClosingRequestMetric($qb, $recurrenceType,
                    $evaluationType, $recurrenceCount),
        };
    }

    //
    protected function applyGreetingActivityReportMetric(QueryBuilder $qb, RecurrenceType $recurrenceType,
            EvaluationType $evaluationType, ?int $recurrenceCount): void
    {
        $qb->from('SalesActivityReport')
                ->innerJoin('SalesActivityReport', 'SalesActivitySchedule', 'SalesActivitySchedule',
                        'SalesActivityReport.SalesActivitySchedule_id = SalesActivitySchedule.id')
                ->innerJoin('SalesActivitySchedule', 'CustomerAssignment', 'CustomerAssignment',
                        'SalesActivitySchedule.CustomerAssignment_id = CustomerAssignment.id')
                ->innerJoin('CustomerAssignment', 'GreetingAssignment', 'GreetingAssignment',
                        'GreetingAssignment.CustomerAssignment_id = CustomerAssignment.id');
        $recurrenceType->applyToQuery($qb, 'SalesActivityReport.submitTime', $recurrenceCount);
        $evaluationType->applyToQuery($qb, 'SalesActivityReport.id');
    }

    protected function applyFactFindingActivityReportMetric(QueryBuilder $qb, RecurrenceType $recurrenceType,
            EvaluationType $evaluationType, ?int $recurrenceCount): void
    {
        $qb->from('SalesActivityReport')
                ->innerJoin('SalesActivityReport', 'SalesActivitySchedule', 'SalesActivitySchedule',
                        'SalesActivityReport.SalesActivitySchedule_id = SalesActivitySchedule.id')
                ->innerJoin('SalesActivitySchedule', 'CustomerAssignment', 'CustomerAssignment',
                        'SalesActivitySchedule.CustomerAssignment_id = CustomerAssignment.id')
                ->innerJoin('CustomerAssignment', 'FactFindingAssignment', 'FactFindingAssignment',
                        'FactFindingAssignment.CustomerAssignment_id = CustomerAssignment.id');
        $recurrenceType->applyToQuery($qb, 'SalesActivityReport.submitTime', $recurrenceCount);
        $evaluationType->applyToQuery($qb, 'SalesActivityReport.id');
    }

    protected function applyStrikingActivityReportMetric(QueryBuilder $qb, RecurrenceType $recurrenceType,
            EvaluationType $evaluationType, ?int $recurrenceCount): void
    {
        $qb->from('SalesActivityReport')
                ->innerJoin('SalesActivityReport', 'SalesActivitySchedule', 'SalesActivitySchedule',
                        'SalesActivityReport.SalesActivitySchedule_id = SalesActivitySchedule.id')
                ->innerJoin('SalesActivitySchedule', 'CustomerAssignment', 'CustomerAssignment',
                        'SalesActivitySchedule.CustomerAssignment_id = CustomerAssignment.id')
                ->innerJoin('CustomerAssignment', 'StrikingAssignment', 'StrikingAssignment',
                        'StrikingAssignment.CustomerAssignment_id = CustomerAssignment.id');
        $recurrenceType->applyToQuery($qb, 'SalesActivityReport.submitTime', $recurrenceCount);
        $evaluationType->applyToQuery($qb, 'SalesActivityReport.id');
    }

    //
    protected function applySuccessfullGreetingMetric(QueryBuilder $qb, RecurrenceType $recurrenceType,
            EvaluationType $evaluationType, ?int $recurrenceCount): void
    {
        $successfullAssignmentStatus = CustomerAssignmentStatus::COMPLETED->value;
        $qb->from('GreetingAssignment')
                ->innerJoin('GreetingAssignment', 'CustomerAssignment', 'CustomerAssignment',
                        'GreetingAssignment.CustomerAssignment_id = CustomerAssignment.id')
                ->andWhere($qb->expr()->eq('GreetingAssignment.status', "'{$successfullAssignmentStatus}'"));
        $recurrenceType->applyToQuery($qb, 'CustomerAssignment.completedTime', $recurrenceCount);
        $evaluationType->applyToQuery($qb, 'GreetingAssignment.id');
    }

    protected function applySuccessfullFactFindingMetric(QueryBuilder $qb, RecurrenceType $recurrenceType,
            EvaluationType $evaluationType, ?int $recurrenceCount): void
    {
        $successfullAssignmentStatus = CustomerAssignmentStatus::COMPLETED->value;
        $qb->from('FactFindingAssignment')
                ->innerJoin('FactFindingAssignment', 'CustomerAssignment', 'CustomerAssignment',
                        'FactFindingAssignment.CustomerAssignment_id = CustomerAssignment.id')
                ->andWhere($qb->expr()->eq('FactFindingAssignment.status', "'{$successfullAssignmentStatus}'"));
        $recurrenceType->applyToQuery($qb, 'CustomerAssignment.completedTime', $recurrenceCount);
        $evaluationType->applyToQuery($qb, 'FactFindingAssignment.id');
    }

    protected function applyApprovedClosingRequestMetric(QueryBuilder $qb, RecurrenceType $recurrenceType,
            EvaluationType $evaluationType, ?int $recurrenceCount): void
    {
        $approvedClosingRequestStatus = ManagementApprovalStatus::APPROVED->value;
        $qb->from('ClosingRequest')
                ->innerJoin('ClosingRequest', 'StrikingAssignment', 'StrikingAssignment',
                        'ClosingRequest.StrikingAssignment_id = StrikingAssignment.id')
                ->andWhere($qb->expr()->eq('ClosingRequest.status', "'{$approvedClosingRequestStatus}'"));
        $recurrenceType->applyToQuery($qb, 'ClosingRequest.createdTime', $recurrenceCount);
        $evaluationType->applyToQuery($qb, 'ClosingRequest.transactionValue');
    }
}
