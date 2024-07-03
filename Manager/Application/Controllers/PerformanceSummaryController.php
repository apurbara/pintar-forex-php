<?php

namespace Manager\Application\Controllers;

use Manager\Domain\DependencyModel\CompanyMetric;
use Manager\Domain\DependencyModel\SalesPerformanceMetric;
use Manager\Domain\DependencyModel\SalesRank;
use Manager\Domain\Model\Manager;
use Manager\Domain\Task\PerformanceSummary\ViewAllCompanyMetricSummary;
use Manager\Domain\Task\PerformanceSummary\ViewAllSalesPerformanceMetricSummary;
use Manager\Domain\Task\PerformanceSummary\ViewAllSalesRankSummary;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrinePerformanceSummaryRepository;
use Resources\Domain\TaskPayload\ViewPayload;

class PerformanceSummaryController extends BaseController
{

    protected function repository(): DoctrinePerformanceSummaryRepository
    {
        return new DoctrinePerformanceSummaryRepository($this->em);
    }

    //
    public function viewAllCompanyMetricSummary(Manager $manager)
    {
        $companyMetricRepository = $this->em->getRepository(CompanyMetric::class);
        $task = new ViewAllCompanyMetricSummary($this->repository(), $companyMetricRepository);
        $payload = new ViewPayload();

        $manager->executeTask($task, $payload);
        return $payload->result;
    }

    public function viewAllSalesRankSummary(Manager $manager)
    {
        $salesRankRepository = $this->em->getRepository(SalesRank::class);
        $task = new ViewAllSalesRankSummary($this->repository(), $salesRankRepository);
        $payload = new ViewPayload();

        $manager->executeTask($task, $payload);
        return $payload->result;
    }

    public function viewAllSalesPerformanceMetricSummary(Manager $manager)
    {
        $salesPerformanceMetricRepository = $this->em->getRepository(SalesPerformanceMetric::class);
        $task = new ViewAllSalesPerformanceMetricSummary($this->repository(), $salesPerformanceMetricRepository);
        $payload = new ViewPayload();

        $manager->executeTask($task, $payload);
        return $payload->result;
    }
}
