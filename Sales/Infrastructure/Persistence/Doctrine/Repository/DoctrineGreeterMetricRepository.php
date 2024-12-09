<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Sales\Domain\Task\Dependency\GreeterMetricRepository;

class DoctrineGreeterMetricRepository extends DoctrineEntityRepository implements GreeterMetricRepository
{
    
    public function allActive(): array
    {
        return $this->findBy([
            'disabled' => false,
        ]);
    }
}
