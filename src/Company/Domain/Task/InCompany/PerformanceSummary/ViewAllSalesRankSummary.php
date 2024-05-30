<?php

namespace Company\Domain\Task\InCompany\PerformanceSummary;

use Company\Domain\Model\PersonnelHavingManagerAssignmentTaskInCompany;
use Company\Domain\Task\InCompany\SalesRank\SalesRankRepository;
use Resources\Domain\TaskPayload\ViewPayload;

class ViewAllSalesRankSummary implements PersonnelHavingManagerAssignmentTaskInCompany
{

    public function __construct(
            protected PerformanceSummaryRepository $repository, protected SalesRankRepository $salesRankRepository)
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
        foreach ($this->salesRankRepository->allActive() as $salesRank) {
            $result[] = $this->repository->summaryOfSalesRank($salesRank);
        }
        $payload->setResult($result);
    }
}
