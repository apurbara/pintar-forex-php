<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\GreeterMetric;
use Company\Domain\Task\GreeterMetric\GreeterMetricRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;

class DoctrineGreeterMetricRepository extends DoctrineEntityRepository implements GreeterMetricRepository
{

    public function add(GreeterMetric $greeterMetric): void
    {
        $this->persist($greeterMetric);
    }

    public function ofId(string $id): GreeterMetric
    {
        return $this->findOneByIdOrDie($id);
    }

    //
    public function aGreeterMetric(string $id)
    {
        return $this->queryOneById($id);
    }

    public function greeterMetricList(array $paginationSchema)
    {
        return $this->queryPaginationList($paginationSchema);
    }
}
