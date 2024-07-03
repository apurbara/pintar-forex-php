<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;

class DoctrineSalesPerformanceMetricEvaluationRepository extends DoctrineEntityRepository
{
    protected function createCoreQueryBuilder(): QueryBuilder
    {
        $qb = parent::createCoreQueryBuilder();
        $qb->andWhere($qb->expr()->eq($this->getTableName() . ".removed", 0));
        return $qb;
    }
}
