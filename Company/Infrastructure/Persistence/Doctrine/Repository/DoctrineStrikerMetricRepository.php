<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\StrikerMetric;
use Company\Domain\Task\StrikerMetric\StrikerMetricRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;

class DoctrineStrikerMetricRepository extends DoctrineEntityRepository implements StrikerMetricRepository
{

    public function add(StrikerMetric $strikerMetric): void
    {
        $this->persist($strikerMetric);
    }

    public function ofId(string $id): StrikerMetric
    {
        return $this->findOneByIdOrDie($id);
    }

    //
    public function aStrikerMetric(string $id)
    {
        return $this->queryOneById($id);
    }

    public function strikerMetricList(array $paginationSchema)
    {
        return $this->queryPaginationList($paginationSchema);
    }
}
