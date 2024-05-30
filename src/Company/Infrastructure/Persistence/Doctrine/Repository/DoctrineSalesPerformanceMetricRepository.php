<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Task\InCompany\SalesPerformanceMetric\SalesPerformanceMetricRepository;
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
}
