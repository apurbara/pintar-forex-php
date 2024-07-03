<?php

namespace Company\Domain\Task\PerformanceSummary;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Task\CompanyMetric\CompanyMetricRepository;
use Resources\Domain\TaskPayload\ViewPayload;

class ViewAllCompanyMetricSummary implements AdminTaskInCompany
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
