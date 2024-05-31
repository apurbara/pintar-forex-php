<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;

class DoctrineSalesPerformanceMetricEvaluationRepository extends DoctrineEntityRepository
{
    protected function createCoreQueryBuilder(): \Doctrine\DBAL\Query\QueryBuilder
    {
        $qb = parent::createCoreQueryBuilder();
        $qb->andWhere($qb->expr()->eq($this->getTableName() . ".removed", 0));
        return $qb;
    }
}
