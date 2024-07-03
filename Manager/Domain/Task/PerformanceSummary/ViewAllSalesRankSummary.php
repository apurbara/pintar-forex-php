<?php

namespace Manager\Domain\Task\PerformanceSummary;

use Manager\Domain\Model\Manager;
use Manager\Domain\Task\Dependency\SalesRankRepository;
use Manager\Domain\Task\ManagerTask;
use Resources\Domain\TaskPayload\ViewPayload;

class ViewAllSalesRankSummary implements ManagerTask
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
    public function executeByManager(Manager $manager, $payload): void
    {
        $result = [];
        foreach ($this->salesRankRepository->allActive() as $salesRank) {
            $result[] = $this->repository->summaryOfSalesRankBelongsToManager($manager->getId(), $salesRank);
        }
        $payload->setResult($result);
    }
}
