<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\CompanyMetric;
use Company\Domain\Task\CompanyMetric\CompanyMetricRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;

class DoctrineCompanyMetricRepository extends DoctrineEntityRepository implements CompanyMetricRepository
{

    public function allActive(): array
    {
        return $this->findBy([
                    'disabled' => false
        ]);
    }

    public function ofId(string $id): CompanyMetric
    {
        return $this->findOneByIdOrDie($id);
    }

    public function add(CompanyMetric $companyMetric): void
    {
        $this->persist($companyMetric);
    }

    //
    public function aCompanyMetric(string $id): array
    {
        return $this->queryOneById($id);
    }

    public function companyMetricList(array $paginationSchema): array
    {
        return $this->queryPaginationList($paginationSchema);
    }
}
