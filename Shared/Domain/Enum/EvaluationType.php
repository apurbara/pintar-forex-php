<?php

namespace Shared\Domain\Enum;

use Doctrine\DBAL\Query\QueryBuilder;

enum EvaluationType: string
{
    case COUNT = 'COUNT';
    case SUM = 'SUM';
    case AVG = 'AVG';
    case MIN = 'MIN';
    case MAX = 'MAX';
    
    public function applyToQuery(QueryBuilder $qb, string $metricEvaluationColumn, ?string $alias = 'achievement'): void
    {
        $qb->addSelect("{$this->value}($metricEvaluationColumn) '$alias'");
    }
}
