<?php

namespace Shared\Domain\Enum;

use DateTime;
use Doctrine\DBAL\Query\QueryBuilder;

enum RecurrenceType: string
{

    case ONCE = 'ONCE';
    case DAILY = 'DAILY';
    case WEEKLY = 'WEEKLY';
    case MONTHLY = 'MONTHLY';
    case YEARLY = 'YEARLY';

    public function applyToQuery(QueryBuilder $qb, string $metricTimeColumn, ?int $recurrenceCount): void
    {
        match ($this) {
            RecurrenceType::ONCE => void,
            RecurrenceType::DAILY => $this->applyDailyEvaluation($qb, $metricTimeColumn, $recurrenceCount),
            RecurrenceType::WEEKLY => $this->applyWeeklyEvaluation($qb, $metricTimeColumn, $recurrenceCount),
            RecurrenceType::MONTHLY => $this->applyMonthlyEvaluation($qb, $metricTimeColumn, $recurrenceCount),
            RecurrenceType::YEARLY => $this->applyYearlyEvaluation($qb, $metricTimeColumn, $recurrenceCount),
        };
    }

    private function applyDailyEvaluation(QueryBuilder $qb, string $metricTimeColumn, ?int $recurrenceCount): void
    {
        $qb->addSelect("DATE_FORMAT($metricTimeColumn, '%Y-%m-&d') evaluationTime")
                ->addSelect("'DAILY' reccurenceType")
                ->addSelect("DAILY reccurenceType")
                ->addGroupBy("DATE_FORMAT($metricTimeColumn, '%Y-%m-&d')");

        $recurrenceCount = $recurrenceCount ?? 1;
        $endTime = (new DateTime())->format('Ymd');
        $startTime = (new DateTime("-{$recurrenceCount} days"))->format('Ymd');
        $qb->andWhere($qb->expr()->lte("DATE_FORMAT($metricTimeColumn, '%Y%m&d')", $endTime))
                ->andWhere($qb->expr()->gt("DATE_FORMAT($metricTimeColumn, '%Y%m&d')", $startTime));
    }

    private function applyWeeklyEvaluation(QueryBuilder $qb, string $metricTimeColumn, ?int $recurrenceCount): void
    {
        $qb->addSelect("DATE_FORMAT($metricTimeColumn, '%Y-%u') evaluationTime")
                ->addSelect("'WEEKLY' reccurenceType")
                ->addGroupBy("DATE_FORMAT($metricTimeColumn, '%Y-%u')");

        $recurrenceCount = $recurrenceCount ?? 1;
        $endTime = (new DateTime())->format('YW');
        $startTime = (new DateTime("-{$recurrenceCount} weeks"))->format('YW');

        $qb->andWhere($qb->expr()->lte("DATE_FORMAT($metricTimeColumn, '%Y%u')", $endTime))
                ->andWhere($qb->expr()->gt("DATE_FORMAT($metricTimeColumn, '%Y%u')", $startTime));
    }

    private function applyMonthlyEvaluation(QueryBuilder $qb, string $metricTimeColumn, ?int $recurrenceCount): void
    {
        $qb->addSelect("DATE_FORMAT($metricTimeColumn, '%Y-%m') evaluationTime")
                ->addSelect("'MONTHLY' reccurenceType")
                ->addGroupBy("DATE_FORMAT($metricTimeColumn, '%Y-%m')");
        
        $recurrenceCount = $recurrenceCount ?? 1;
        $endTime = (new DateTime())->format('Ym');
        $startTime = (new \DateTime("first day of -{$recurrenceCount} month"))->format('Ym');
        $qb->andWhere($qb->expr()->lte("DATE_FORMAT($metricTimeColumn, '%Y%m')", $endTime))
                ->andWhere($qb->expr()->gt("DATE_FORMAT($metricTimeColumn, '%Y%m')", $startTime));
    }

    private function applyYearlyEvaluation(QueryBuilder $qb, string $metricTimeColumn, ?int $recurrenceCount): void
    {
        $qb->addSelect("DATE_FORMAT($metricTimeColumn, '%Y') evaluationTime")
                ->addSelect("'YEARLY' reccurenceType")
                ->addGroupBy("DATE_FORMAT($metricTimeColumn, '%Y')");
        
        $recurrenceCount = $recurrenceCount ?? 1;
        $endTime = (new DateTime())->format('Y');
        $startTime = (new \DateTime("first day of -{$recurrenceCount} year"))->format('Y');
        $qb->andWhere($qb->expr()->lte("DATE_FORMAT($metricTimeColumn, '%Y')", $endTime))
                ->andWhere($qb->expr()->gt("DATE_FORMAT($metricTimeColumn, '%Y')", $startTime));
    }
}
