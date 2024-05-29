<?php

namespace SharedContext\Domain\Enum;

use Doctrine\DBAL\Query\QueryBuilder;

enum EvaluationType: string
{
    case COUNT = 'COUNT';
    case SUM = 'SUM';
    case AVG = 'AVG';
    case MIN = 'MIN';
    case MAX = 'MAX';
    
    public function applyToQueryBuilder(QueryBuilder $qb, string $metricEvaluationColumn): void
    {
        $qb->addSelect("{$this->value}($metricEvaluationColumn) 'value'");
    }
}
