<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\CompanyMetric;
use Company\Domain\Model\CompanyUser;
use Company\Domain\Model\SalesPerformanceMetric;
use Company\Domain\Model\SalesRank;
use Company\Domain\Task\PerformanceSummary\ViewAllCompanyMetricSummary;
use Company\Domain\Task\PerformanceSummary\ViewAllSalesPerformanceMetricSummary;
use Company\Domain\Task\PerformanceSummary\ViewAllSalesRankSummary;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrinePerformanceSummaryRepository;
use Resources\Domain\TaskPayload\ViewPayload;

class PerformanceSummaryController extends BaseController
{

    protected function repository(): DoctrinePerformanceSummaryRepository
    {
        return new DoctrinePerformanceSummaryRepository($this->em);
    }

    //
    public function viewAllCompanyMetricSummary(CompanyUser $user)
    {
        $companyMetricRepository = $this->em->getRepository(CompanyMetric::class);
        $task = new ViewAllCompanyMetricSummary($this->repository(), $companyMetricRepository);
        $payload = new ViewPayload();

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    public function viewAllSalesRankSummary(CompanyUser $user)
    {
        $salesRankRepository = $this->em->getRepository(SalesRank::class);
        $task = new ViewAllSalesRankSummary($this->repository(), $salesRankRepository);
        $payload = new ViewPayload();

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    public function viewAllSalesPerformanceMetricSummary(CompanyUser $user)
    {
        $salesPerformanceMetricRepository = $this->em->getRepository(SalesPerformanceMetric::class);
        $task = new ViewAllSalesPerformanceMetricSummary($this->repository(), $salesPerformanceMetricRepository);
        $payload = new ViewPayload();

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
