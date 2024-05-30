<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Task\InCompany\CompanyMetric\CompanyMetricRepository;
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
