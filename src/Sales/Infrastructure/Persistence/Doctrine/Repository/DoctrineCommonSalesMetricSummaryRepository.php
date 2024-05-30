<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityManager;
use Sales\Domain\DependencyModel\CommonSalesMetric;
use Sales\Domain\Task\BySales\CommonSalesMetricSummary\CommonSalesMetricSummaryRepository;

class DoctrineCommonSalesMetricSummaryRepository implements CommonSalesMetricSummaryRepository
{

    public function __construct(protected EntityManager $em)
    {
        
    }

    public function summaryOfCommonSalesMetricBelongsToSales(string $salesId, CommonSalesMetric $commonSalesMetric): ?array
    {
        return $commonSalesMetric->fetchSummaryResult($this->em->getConnection(), $salesId);
    }
}
