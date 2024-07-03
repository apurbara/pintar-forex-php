<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\SalesPerformanceMetric;
use Company\Domain\Task\SalesPerformanceMetric\SalesPerformanceMetricRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;

class DoctrineSalesPerformanceMetricRepository extends DoctrineEntityRepository
        implements SalesPerformanceMetricRepository
{

    public function allActive(): array
    {
        return $this->findBy([
                    'disabled' => false,
        ]);
    }

    public function add(SalesPerformanceMetric $salesPerformanceMetric): void
    {
        $this->persist($salesPerformanceMetric);
    }

    public function ofId(string $id): SalesPerformanceMetric
    {
        return $this->findOneByIdOrDie($id);
    }

    //
    public function aSalesPerfomanceMetric(string $id)
    {
        return $this->queryOneById($id);
    }

    public function salesPerfomanceMetricList(array $paginationSchema)
    {
        return $this->queryPaginationList($paginationSchema);
    }
}
