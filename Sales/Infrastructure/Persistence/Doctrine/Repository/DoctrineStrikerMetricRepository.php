<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Sales\Domain\Task\Dependency\StrikerMetricRepository;

class DoctrineStrikerMetricRepository extends DoctrineEntityRepository implements StrikerMetricRepository
{
    
    public function allActive(): array
    {
        return $this->findBy([
            'disabled' => false,
        ]);
    }
}
