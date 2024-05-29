<?php

namespace SharedContext\Domain\Enum;

use Doctrine\DBAL\Query\QueryBuilder;

enum RecurrenceType: string
{
    case ONCE = 'ONCE';
    case DAILY = 'DAILY';
    case WEEKLY = 'WEEKLY';
    case MONTHLY = 'MONTHLY';
    case YEARLY = 'YEARLY';
    
    public function applyToQueryBuilder(QueryBuilder $qb, string $metricTimeColumn, ?int $recurrenceCount): void
    {
        match ($this){
            RecurrenceType::ONCE => void,
            RecurrenceType::DAILY => $this->applyDailyEvaluation($qb, $metricTimeColumn, $recurrenceCount),
            RecurrenceType::WEEKLY=> $this->applyWeeklyEvaluation($qb, $metricTimeColumn, $recurrenceCount),
            RecurrenceType::MONTHLY=> $this->applyMonthlyEvaluation($qb, $metricTimeColumn, $recurrenceCount),
            RecurrenceType::YEARLY=> $this->applyYearlyEvaluation($qb, $metricTimeColumn, $recurrenceCount),
        };
    }
    
    private function applyDailyEvaluation(QueryBuilder $qb, string $metricTimeColumn, ?int $recurrenceCount): void
    {
        $recurrenceCount = $recurrenceCount ?? 0;
        $endTime = (new \DateTime())->format('Ymd');
        $startTime = (new \DateTime("-{$recurrenceCount} days"))->format('Ymd');
        $qb->addSelect("DATE_FORMAT($metricTimeColumn, '%Y-%m-&d') 'time'")
                ->andWhere($qb->expr()->lte("DATE_FORMAT($metricTimeColumn, '%Y%m&d')", $endTime))
                ->andWhere($qb->expr()->gt("DATE_FORMAT($metricTimeColumn, '%Y%m&d')", $startTime))
                ->addGroupBy("DATE_FORMAT($metricTimeColumn, '%Y-%m-&d')");
    }
    
    private function applyWeeklyEvaluation(QueryBuilder $qb, string $metricTimeColumn, ?int $recurrenceCount): void
    {
        $recurrenceCount = $recurrenceCount ?? 0;
        $endTime = (new \DateTime())->format('YW');
        $startTime = (new \DateTime("-{$recurrenceCount} weeks"))->format('YW');
        $qb->addSelect("DATE_FORMAT($metricTimeColumn, '%Y-%u') 'time'")
                ->andWhere($qb->expr()->lte("DATE_FORMAT($metricTimeColumn, '%Y%u')", $endTime))
                ->andWhere($qb->expr()->gt("DATE_FORMAT($metricTimeColumn, '%Y%u')", $startTime))
                ->addGroupBy("DATE_FORMAT($metricTimeColumn, '%Y-%u')");
    }
    
    private function applyMonthlyEvaluation(QueryBuilder $qb, string $metricTimeColumn, ?int $recurrenceCount): void
    {
        $recurrenceCount = $recurrenceCount ?? 0;
        $endTime = (new \DateTime())->format('Ym');
        $startTime = (new \DateTime("-{$recurrenceCount} months"))->format('Ym');
        $qb->addSelect("DATE_FORMAT($metricTimeColumn, '%Y-%m') 'time'")
                ->andWhere($qb->expr()->lte("DATE_FORMAT($metricTimeColumn, '%Y%m')", $endTime))
                ->andWhere($qb->expr()->gt("DATE_FORMAT($metricTimeColumn, '%Y%m')", $startTime))
                ->addGroupBy("DATE_FORMAT($metricTimeColumn, '%Y-%m')");
    }
    
    private function applyYearlyEvaluation(QueryBuilder $qb, string $metricTimeColumn, ?int $recurrenceCount): void
    {
        $recurrenceCount = $recurrenceCount ?? 0;
        $endTime = (new \DateTime())->format('Y');
        $startTime = (new \DateTime("-{$recurrenceCount} years"))->format('Y');
        $qb->addSelect("DATE_FORMAT($metricTimeColumn, '%Y') 'time'")
                ->andWhere($qb->expr()->lte("DATE_FORMAT($metricTimeColumn, '%Y')", $endTime))
                ->andWhere($qb->expr()->gt("DATE_FORMAT($metricTimeColumn, '%Y')", $startTime))
                ->addGroupBy("DATE_FORMAT($metricTimeColumn, '%Y')");
    }
}
