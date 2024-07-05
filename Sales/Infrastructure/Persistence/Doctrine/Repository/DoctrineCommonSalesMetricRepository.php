<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Sales\Domain\Task\Dependency\CommonSalesMetricRepository;

class DoctrineCommonSalesMetricRepository extends DoctrineEntityRepository implements CommonSalesMetricRepository
{
    
    public function allActive(): array
    {
        return $this->findBy([
            'disabled' => false,
        ]);
    }
}
