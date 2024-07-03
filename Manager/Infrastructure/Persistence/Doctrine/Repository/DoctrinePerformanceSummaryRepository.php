<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityManager;
use Manager\Domain\DependencyModel\CompanyMetric;
use Manager\Domain\DependencyModel\SalesPerformanceMetric;
use Manager\Domain\DependencyModel\SalesRank;
use Manager\Domain\Task\PerformanceSummary\PerformanceSummaryRepository;

class DoctrinePerformanceSummaryRepository implements PerformanceSummaryRepository
{

    public function __construct(protected EntityManager $em)
    {
        
    }

    public function summaryOfCompanyMetricBelongsToManager(string $managerId, CompanyMetric $companyMetric): ?array
    {
        return $companyMetric->fetchSummaryResult($this->em->getConnection(), $managerId);
    }

    public function summaryOfSalesPerformanceMetricBelongsToManager(
            string $managerId, SalesPerformanceMetric $salesPerformanceMetric): ?array
    {
        return $salesPerformanceMetric->fetchSummaryResult($this->em->getConnection(), $managerId);
    }

    public function summaryOfSalesRankBelongsToManager(string $managerId, SalesRank $salesRank): ?array
    {
        return $salesRank->fetchSummaryResult($this->em->getConnection(), $managerId);
    }
}
