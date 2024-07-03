<?php

namespace Shared\Domain\Enum;

use Doctrine\DBAL\Query\QueryBuilder;

enum QueryOrder: string
{
    case ASC = 'ASC';
    case DESC = 'DESC';
    
    public function applyToQuery(QueryBuilder $qb, string $sortingColumn): void
    {
        $qb->addOrderBy($sortingColumn, $this->value);
    }
}
