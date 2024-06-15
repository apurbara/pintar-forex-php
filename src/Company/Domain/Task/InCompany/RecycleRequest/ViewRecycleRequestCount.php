<?php

namespace Company\Domain\Task\InCompany\RecycleRequest;

use Company\Domain\Model\ManagerTaskInCompany;
use Resources\Domain\TaskPayload\ViewSummaryPayload;

class ViewRecycleRequestCount implements ManagerTaskInCompany
{
    public function __construct(protected RecycleRequestRepository $repository)
    {
    }
    
    /**
     * 
     * @param ViewSummaryPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->recycleRequestCount($payload->searchSchema);
        $payload->setResult($result);
    }
}
