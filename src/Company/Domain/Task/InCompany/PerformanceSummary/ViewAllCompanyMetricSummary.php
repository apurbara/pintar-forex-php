<?php

namespace Company\Domain\Task\InCompany\PerformanceSummary;

use Company\Domain\Model\ManagerTaskInCompany;
use Company\Domain\Task\InCompany\CompanyMetric\CompanyMetricRepository;
use Resources\Domain\TaskPayload\ViewPayload;

class ViewAllCompanyMetricSummary implements ManagerTaskInCompany
{

    public function __construct(
            protected PerformanceSummaryRepository $repository,
            protected CompanyMetricRepository $companyMetricRepository)
    {
        
    }

    /**
     * 
     * @param ViewPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = [];
        foreach ($this->companyMetricRepository->allActive() as $companyMetric) {
            $result[] = $this->repository->summaryOfCompanyMetric($companyMetric);
        }
        $payload->setResult($result);
    }
}
