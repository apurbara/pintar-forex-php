<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use Manager\Domain\Task\Dependency\SalesPerformanceMetricRepository;
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
