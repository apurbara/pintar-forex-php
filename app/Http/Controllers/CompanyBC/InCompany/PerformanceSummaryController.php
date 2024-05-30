<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use Company\Domain\Model\CompanyMetric;
use Company\Domain\Model\SalesPerformanceMetric;
use Company\Domain\Model\SalesRank;
use Company\Domain\Task\InCompany\PerformanceSummary\ViewAllCompanyMetricSummary;
use Company\Domain\Task\InCompany\PerformanceSummary\ViewAllSalesPerformanceMetricSummary;
use Company\Domain\Task\InCompany\PerformanceSummary\ViewAllSalesRankSummary;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrinePerformanceSummaryRepository;
use Resources\Domain\TaskPayload\ViewPayload;

class PerformanceSummaryController extends Controller
{

    protected function repository(): DoctrinePerformanceSummaryRepository
    {
        return new DoctrinePerformanceSummaryRepository($this->em);
    }

    //
    public function viewAllCompanyMetricSummary(CompanyUserRoleInterface $user)
    {
        $companyMetricRepository = $this->em->getRepository(CompanyMetric::class);
        $task = new ViewAllCompanyMetricSummary($this->repository(), $companyMetricRepository);
        $payload = new ViewPayload();

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    public function viewAllSalesRankSummary(CompanyUserRoleInterface $user)
    {
        $salesRankRepository = $this->em->getRepository(SalesRank::class);
        $task = new ViewAllSalesRankSummary($this->repository(), $salesRankRepository);
        $payload = new ViewPayload();

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    public function viewAllSalesPerformanceMetricSummary(CompanyUserRoleInterface $user)
    {
        $salesPerformanceMetricRepository = $this->em->getRepository(SalesPerformanceMetric::class);
        $task = new ViewAllSalesPerformanceMetricSummary($this->repository(), $salesPerformanceMetricRepository);
        $payload = new ViewPayload();

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
