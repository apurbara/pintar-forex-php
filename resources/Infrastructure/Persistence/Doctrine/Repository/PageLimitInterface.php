<?php

namespace Resources\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;

interface PageLimitInterface
{

    public function paginateResult(QueryBuilder $qb): array;
}
