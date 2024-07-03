<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use Manager\Domain\Task\Dependency\CompanyMetricRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;

class DoctrineCompanyMetricRepository extends DoctrineEntityRepository implements CompanyMetricRepository
{

    public function allActive(): array
    {
        return $this->findBy([
                    'disabled' => false
        ]);
    }
}
