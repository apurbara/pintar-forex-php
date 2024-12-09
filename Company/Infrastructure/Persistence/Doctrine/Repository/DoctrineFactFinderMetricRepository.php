<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\FactFinderMetric;
use Company\Domain\Task\FactFinderMetric\FactFinderMetricRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;

class DoctrineFactFinderMetricRepository extends DoctrineEntityRepository implements FactFinderMetricRepository
{
    
    public function add(FactFinderMetric $factFinderMetric): void
    {
        $this->persist($factFinderMetric);
    }

    public function ofId(string $id): FactFinderMetric
    {
        return $this->findOneByIdOrDie($id);
    }
    
    //
    public function aFactFinderMetric(string $id)
    {
        return $this->queryOneById($id);
    }


    public function factFinderMetricList(array $paginationSchema)
    {
        return $this->queryPaginationList($paginationSchema);
    }
}
