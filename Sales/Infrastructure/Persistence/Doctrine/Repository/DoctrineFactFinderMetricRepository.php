<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Sales\Domain\Task\Dependency\FactFinderMetricRepository;

class DoctrineFactFinderMetricRepository extends DoctrineEntityRepository implements FactFinderMetricRepository
{
    
    public function allActive(): array
    {
        return $this->findBy([
            'disabled' => false,
        ]);
    }
}
