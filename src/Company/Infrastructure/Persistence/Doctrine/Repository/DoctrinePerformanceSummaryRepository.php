<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\CompanyMetric;
use Company\Domain\Model\SalesPerformanceMetric;
use Company\Domain\Model\SalesRank;
use Company\Domain\Task\InCompany\PerformanceSummary\PerformanceSummaryRepository;
use Doctrine\ORM\EntityManager;

class DoctrinePerformanceSummaryRepository implements PerformanceSummaryRepository
{

    public function __construct(protected EntityManager $em)
    {
        
    }

    public function summaryOfCompanyMetric(CompanyMetric $companyMetric): ?array
    {
        return $companyMetric->fetchSummaryResult($this->em->getConnection());
    }

    public function summaryOfSalesPerformanceMetric(SalesPerformanceMetric $salesPeformanceMetric): ?array
    {
        return $salesPeformanceMetric->fetchSummaryResult($this->em->getConnection());
    }

    public function summaryOfSalesRank(SalesRank $salesRank): ?array
    {
        return $salesRank->fetchSummaryResult($this->em->getConnection());
    }
}
