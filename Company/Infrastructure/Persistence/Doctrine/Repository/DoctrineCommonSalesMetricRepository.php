<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\CommonSalesMetric;
use Company\Domain\Task\CommonSalesMetric\CommonSalesMetricRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;

class DoctrineCommonSalesMetricRepository extends DoctrineEntityRepository implements CommonSalesMetricRepository
{

    public function add(CommonSalesMetric $commonSalesMetric): void
    {
        $this->persist($commonSalesMetric);
    }

    public function ofId(string $id): CommonSalesMetric
    {
        return $this->findOneByIdOrDie($id);
    }

    //
    public function aCommonSalesMetric(string $id)
    {
        return $this->queryOneById($id);
    }

    public function commonSalesMetricList(array $paginationSchema)
    {
        return $this->queryPaginationList($paginationSchema);
    }
}
