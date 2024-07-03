<?php

namespace Manager\Domain\Task\PerformanceSummary;

use Manager\Domain\Model\Manager;
use Manager\Domain\Task\Dependency\CompanyMetricRepository;
use Manager\Domain\Task\ManagerTask;
use Resources\Domain\TaskPayload\ViewPayload;

class ViewAllCompanyMetricSummary implements ManagerTask
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
    public function executeByManager(Manager $manager, $payload): void
    {
        $result = [];
        foreach ($this->companyMetricRepository->allActive() as $companyMetric) {
            $result[] = $this->repository->summaryOfCompanyMetricBelongsToManager($manager->getId(), $companyMetric);
        }
        $payload->setResult($result);
    }
}
